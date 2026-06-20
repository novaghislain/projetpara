<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">{{ isEdit ? 'Modifier' : 'Planifier' }} une Assemblée Générale</h4>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Type d'AG</label>
                            <select v-model="form.type" class="form-select">
                                <option value="AGO">AGO — Ordinaire</option>
                                <option value="AGE">AGE — Extraordinaire</option>
                                <option value="AGOE">AGOE — Ordinaire et Extraordinaire</option>
                                <option value="CA">CA — Conseil d'Administration</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Année</label>
                            <input v-model="form.annee" type="number" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date de tenue</label>
                            <input v-model="form.date_tenue" type="date" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lieu</label>
                            <input v-model="form.lieu" class="form-control" placeholder="Ex: Siège social, Salle de réunion" />
                        </div>
                    </div>

                    <hr />
                    <h6 class="fw-semibold mb-3">Ordre du jour</h6>
                    <div v-for="(point, i) in form.ordre_du_jour" :key="i" class="input-group mb-2">
                        <input v-model="form.ordre_du_jour[i]" class="form-control" :placeholder="'Point n°' + (i+1)" />
                        <button class="btn btn-outline-danger" @click="form.ordre_du_jour.splice(i, 1)">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" @click="form.ordre_du_jour.push('')">
                        <i class="bi bi-plus me-1"></i> Ajouter un point
                    </button>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary" @click="save" :disabled="saving">
                            {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                        <a href="/juridique/assemblees" class="btn btn-secondary">Annuler</a>
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
const form = ref({
    type: 'AGO',
    annee: new Date().getFullYear(),
    date_tenue: '',
    lieu: 'Siège social',
    ordre_du_jour: [''],
});
const isEdit = ref(false);

async function save() {
    saving.value = true;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/juridique/assemblees', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                ...form.value,
                ordre_du_jour: form.value.ordre_du_jour.filter(p => p.trim()),
            }),
        });
        if (res.ok) {
            window.location.href = '/juridique/assemblees';
        }
    } catch (e) { console.error(e); }
    saving.value = false;
}
</script>
