<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">📚 Bibliothèque d'Actes</h4>
                <div class="d-flex gap-2">
                    <a href="/juridique/bibliotheque/generer" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-text me-1"></i> Générer un acte
                    </a>
                    <a href="/juridique/bibliotheque/create" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Nouveau modèle
                    </a>
                </div>
            </div>

            <div v-if="error" class="alert alert-danger">⚠️ {{ error }}</div>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <select v-model="filtreCategorie" class="form-select form-select-sm">
                        <option value="">Toutes catégories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                </div>
            </div>

            <div class="row g-3">
                <div v-for="acte in filteredList" :key="acte.id" class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark">{{ acte.categorie }}</span>
                                <span v-if="acte.is_validated" class="badge bg-success">Validé</span>
                            </div>
                            <h6 class="fw-semibold">{{ acte.titre }}</h6>
                            <p class="small text-muted mb-2">{{ acte.contenu ? acte.contenu.substring(0, 120) + '...' : '—' }}</p>
                            <div class="small text-muted">
                                <span v-if="acte.variables?.length"><i class="bi bi-gear me-1"></i>{{ acte.variables.length }} variables</span>
                                <span class="ms-2">v{{ acte.version || 1 }}</span>
                            </div>
                            <div class="mt-2 d-flex gap-1">
                                <a :href="'/juridique/bibliotheque/' + acte.id + '/generer'" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-file-earmark-text"></i> Générer
                                </a>
                                <a :href="'/juridique/bibliotheque/' + acte.id + '/edit'" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!filteredList.length" class="col-12 text-center text-muted py-4">Aucun acte</div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const actes = ref([]);
const filtreCategorie = ref('');
const error = ref(null);

const categories = computed(() => [...new Set(actes.value.map(a => a.categorie).filter(Boolean))]);

const filteredList = computed(() => {
    if (!filtreCategorie.value) return actes.value;
    return actes.value.filter(a => a.categorie === filtreCategorie.value);
});

async function load() {
    error.value = null;
    try {
        const res = await fetch('/juridique/bibliotheque');
        if (!res.ok) throw new Error('Erreur ' + res.status);
        actes.value = await res.json();
    } catch (e) {
        console.error(e);
        error.value = 'Impossible de charger la bibliothèque.';
    }
}
onMounted(load);
</script>
