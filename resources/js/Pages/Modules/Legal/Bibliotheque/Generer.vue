<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">📝 Générer un acte</h4>
                <a href="/juridique/bibliotheque" class="btn btn-outline-secondary btn-sm">Annuler</a>
            </div>

            <div class="row g-4">
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">1. Choisir un modèle</h6>
                            <div v-for="m in modeles" :key="m.id" class="border rounded p-2 mb-2" :class="{ 'border-primary': selected === m.id }" style="cursor:pointer" @click="selectModele(m)">
                                <div class="fw-semibold small">{{ m.titre }}</div>
                                <div class="small text-muted">{{ m.categorie }} — v{{ m.version || 1 }}</div>
                            </div>
                            <div v-if="!modeles.length" class="text-muted small">Aucun modèle disponible</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div v-if="selectedModele" class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">2. Remplir les variables — {{ selectedModele.titre }}</h6>

                            <div v-for="v in selectedModele.variables" :key="v" class="mb-2">
                                <label class="form-label small fw-semibold">{{ v }}</label>
                                <input v-model="formValues[v]" class="form-control form-control-sm" :placeholder="v" />
                            </div>

                            <div v-if="!selectedModele.variables?.length" class="text-muted small mb-3">Aucune variable requise</div>

                            <button class="btn btn-success btn-sm mt-2" @click="generer" :disabled="generating">
                                <i class="bi bi-file-earmark-text me-1"></i>
                                {{ generating ? 'Génération...' : 'Générer l\'acte' }}
                            </button>

                            <div v-if="generatedContent" class="mt-4">
                                <hr />
                                <h6 class="fw-semibold">Aperçu</h6>
                                <div class="border rounded p-3 bg-white" style="white-space:pre-wrap;font-size:0.85rem">{{ generatedContent }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="card border-0 shadow-sm">
                        <div class="card-body text-center text-muted py-5">
                            <i class="bi bi-file-earmark-text" style="font-size:2rem"></i>
                            <p class="mt-2">Sélectionnez un modèle à gauche</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const modeles = ref([]);
const selected = ref(null);
const selectedModele = ref(null);
const formValues = ref({});
const generatedContent = ref(null);
const generating = ref(false);

function selectModele(m) {
    selected.value = m.id;
    selectedModele.value = m;
    formValues.value = {};
    generatedContent.value = null;
    if (m.variables) {
        for (const v of m.variables) formValues.value[v] = '';
    }
}

async function generer() {
    generating.value = true;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/bibliotheque/${selectedModele.value.id}/generer`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ variables: formValues.value }),
        });
        if (res.ok) { const data = await res.json(); generatedContent.value = data.contenu; }
    } catch (e) { console.error(e); }
    generating.value = false;
}

async function load() {
    try { const res = await fetch('/juridique/bibliotheque'); modeles.value = await res.json(); }
    catch (e) { console.error(e); }
}
onMounted(load);
</script>
