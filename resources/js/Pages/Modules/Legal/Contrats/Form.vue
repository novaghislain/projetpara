<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">{{ isEdit ? 'Modifier' : 'Nouveau' }} contrat</h4>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Titre du contrat</label>
                            <input v-model="form.titre" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Type</label>
                            <select v-model="form.type" class="form-select">
                                <option value="prestation_service">Prestation de service</option>
                                <option value="vente">Vente</option>
                                <option value="bail_commercial">Bail commercial</option>
                                <option value="travail">Contrat de travail</option>
                                <option value="partenariat">Partenariat</option>
                                <option value="confidentialite_nda">Confidentialité (NDA)</option>
                                <option value="pret">Prêt</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Montant (FCFA)</label>
                            <input v-model="form.montant" type="number" class="form-control" />
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Objet</label>
                            <textarea v-model="form.objet" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date de début</label>
                            <input v-model="form.date_debut" type="date" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date de fin</label>
                            <input v-model="form.date_fin" type="date" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Renouvellement auto</label>
                            <div class="form-check form-switch mt-2">
                                <input v-model="form.renouvellement_auto" type="checkbox" class="form-check-input" />
                                <label class="form-check-label">Oui</label>
                            </div>
                        </div>
                    </div>

                    <hr />
                    <h6 class="fw-semibold mb-2">Parties contractantes</h6>
                    <PartiesTable :parties="form.parties" editable @add="form.parties.push({ nom: '', role:'', email:'', telephone:'' })" @remove="form.parties.splice($event, 1)" />

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary" @click="save" :disabled="saving">
                            {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                        <a href="/juridique/contrats" class="btn btn-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import PartiesTable from '../../../../Components/Legal/PartiesTable.vue';

const saving = ref(false);
const isEdit = ref(false);
const form = ref({
    titre: '', type: 'prestation_service', objet: '',
    montant: null, devise: 'XOF',
    date_debut: '', date_fin: '',
    renouvellement_auto: false, alerte_avant: 30,
    parties: [],
});

async function save() {
    saving.value = true;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/juridique/contrats', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(form.value),
        });
        if (res.ok) window.location.href = '/juridique/contrats';
    } catch (e) { console.error(e); }
    saving.value = false;
}
</script>
