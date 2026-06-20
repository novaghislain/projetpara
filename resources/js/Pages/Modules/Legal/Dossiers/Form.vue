<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Nouveau dossier juridique</h4>
                <a href="/juridique/dossiers" class="btn btn-outline-secondary btn-sm">Annuler</a>
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
                            <input v-model="form.type" class="form-control" placeholder="ex: contentieux, contrat, conseil" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Priorité</label>
                            <select v-model="form.priorite" class="form-select">
                                <option value="basse">Basse</option><option value="moyenne">Moyenne</option>
                                <option value="haute">Haute</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea v-model="form.description" class="form-control" rows="3"></textarea>
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
const form = ref({ titre:'', type:'', priorite:'moyenne', description:'' });
async function save() {
    saving.value = true;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/juridique/dossiers', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify(form.value) });
        if (res.ok) window.location.href = '/juridique/dossiers';
    } catch(e) { console.error(e); }
    saving.value = false;
}
</script>
