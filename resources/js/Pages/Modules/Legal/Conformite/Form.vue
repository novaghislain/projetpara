<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Nouvelle obligation de conformité</h4>
                <a href="/juridique/conformite" class="btn btn-outline-secondary btn-sm">Annuler</a>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Intitulé</label>
                            <input v-model="form.intitule" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Type</label>
                            <select v-model="form.type" class="form-select">
                                <option value="fiscal">Fiscal</option><option value="social">Social</option>
                                <option value="comptable">Comptable</option><option value="juridique">Juridique</option>
                                <option value="environnemental">Environnemental</option><option value="sécurité">Sécurité</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Organisme</label>
                            <input v-model="form.organisme" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date d'échéance</label>
                            <input v-model="form.date_echeance" type="date" class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Périodicité</label>
                            <select v-model="form.periodicite" class="form-select">
                                <option value="unique">Unique</option><option value="mensuelle">Mensuelle</option>
                                <option value="trimestrielle">Trimestrielle</option><option value="semestrielle">Semestrielle</option>
                                <option value="annuelle">Annuelle</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Alerte (jours avant)</label>
                            <input v-model="form.alerte_avant" type="number" class="form-control" value="30" />
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea v-model="form.notes" class="form-control" rows="2"></textarea>
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
const form = ref({ intitule:'', type:'juridique', organisme:'', date_echeance:'', periodicite:'annuelle', alerte_avant:30, notes:'' });
async function save() {
    saving.value = true;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/juridique/conformite', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify(form.value) });
        if (res.ok) window.location.href = '/juridique/conformite';
    } catch(e) { console.error(e); }
    saving.value = false;
}
</script>
