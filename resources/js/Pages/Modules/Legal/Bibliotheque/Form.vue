<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">{{ isEdit ? 'Modifier' : 'Nouveau' }} modèle d'acte</h4>
                <a href="/juridique/bibliotheque" class="btn btn-outline-secondary btn-sm">Annuler</a>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Titre</label>
                            <input v-model="form.titre" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Catégorie</label>
                            <input v-model="form.categorie" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Type de société</label>
                            <select v-model="form.type_societe" class="form-select">
                                <option value="">Tous</option><option value="SARL">SARL</option>
                                <option value="SA">SA</option><option value="SAS">SAS</option>
                                <option value="SNC">SNC</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Contenu (utilisez {{ variable }} pour les champs dynamiques)</label>
                            <textarea v-model="form.contenu" class="form-control" rows="8"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Variables (séparées par des virgules)</label>
                            <input v-model="variablesStr" class="form-control" placeholder="ex: nom_societe, capital, siege_social" />
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
import { ref, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
const saving = ref(false);
const isEdit = ref(false);
const variablesStr = ref('');
const form = ref({ titre:'', categorie:'', type_societe:'', contenu:'' });
async function save() {
    saving.value = true;
    try {
        form.value.variables = variablesStr.value.split(',').map(v => v.trim()).filter(Boolean);
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/juridique/bibliotheque', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify(form.value) });
        if (res.ok) window.location.href = '/juridique/bibliotheque';
    } catch(e) { console.error(e); }
    saving.value = false;
}
</script>
