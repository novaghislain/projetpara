<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Nouveau litige</h4>
                <a href="/juridique/contentieux" class="btn btn-outline-secondary btn-sm">Annuler</a>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Titre</label>
                            <input v-model="form.titre" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Type</label>
                            <select v-model="form.type" class="form-select">
                                <option value="civil">Civil</option><option value="commercial">Commercial</option>
                                <option value="social">Social</option><option value="fiscal">Fiscal</option>
                                <option value="pénal">Pénal</option><option value="administratif">Administratif</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nature</label>
                            <select v-model="form.nature" class="form-select">
                                <option value="demandeur">Demandeur</option>
                                <option value="défendeur">Défendeur</option>
                                <option value="tiers">Tiers</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Partie adverse</label>
                            <input v-model="form.partie_adverse" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tribunal</label>
                            <input v-model="form.tribunal" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Avocat</label>
                            <input v-model="form.avocat" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Montant du litige</label>
                            <input v-model="form.montant_litige" type="number" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date de saisine</label>
                            <input v-model="form.date_saisine" type="date" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Prochaine audience</label>
                            <input v-model="form.prochaine_audience" type="date" class="form-control" />
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary" @click="save" :disabled="saving">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
import { ref } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
const saving = ref(false);
const form = ref({ titre:'', type:'civil', nature:'demandeur', partie_adverse:'', tribunal:'', avocat:'', montant_litige:null, date_saisine:'', prochaine_audience:'' });
async function save() {
    saving.value = true;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/juridique/contentieux', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify(form.value) });
        if (res.ok) window.location.href = '/juridique/contentieux';
    } catch(e) { console.error(e); }
    saving.value = false;
}
</script>
