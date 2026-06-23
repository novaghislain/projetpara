<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">⚖️ Contentieux & Litiges</h4>
                <a href="/juridique/contentieux/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nouveau litige
                </a>
            </div>

            <div v-if="error" class="alert alert-danger">⚠️ {{ error }}</div>

            <div class="d-flex gap-2 mb-3 flex-wrap">
                <button v-for="s in statutsFiltres" :key="s.key" class="btn btn-sm" :class="filtre === s.key ? 'btn-dark' : 'btn-outline-secondary'" @click="filtre = s.key">
                    {{ s.label }} ({{ s.count }})
                </button>
            </div>

            <div class="row g-3">
                <div v-for="l in filteredList" :key="l.id" class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <code>{{ l.reference }}</code>
                                <ContratStatusBadge :statut="l.statut" :label="l.statut" />
                            </div>
                            <h6 class="fw-semibold">{{ l.titre }}</h6>
                            <div class="small text-muted mb-2">{{ l.type }} — {{ l.nature }}</div>
                            <div class="d-flex justify-content-between small">
                                <span><i class="bi bi-bank me-1"></i>{{ l.tribunal || '—' }}</span>
                                <span><i class="bi bi-calendar me-1"></i>{{ formatDate(l.prochaine_audience) }}</span>
                            </div>
                            <div v-if="l.montant_litige" class="mt-2 small">
                                <span class="text-danger fw-semibold">{{ formatCurrency(l.montant_litige) }}</span>
                            </div>
                            <a :href="'/juridique/contentieux/' + l.id" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div v-if="!filteredList.length" class="col-12 text-center text-muted py-4">Aucun litige</div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import ContratStatusBadge from '../../../../Components/Legal/ContratStatusBadge.vue';

const litiges = ref([]);
const filtre = ref('tous');
const error = ref(null);

const counts = computed(() => ({
    tous: litiges.value.length,
    en_cours: litiges.value.filter(l => ['assignation','instruction','plaidoirie','en_attente'].includes(l.statut)).length,
    clos: litiges.value.filter(l => ['gagné','perdu','transigé','classé'].includes(l.statut)).length,
}));

const statutsFiltres = computed(() => [
    { key: 'tous', label: 'Tous', count: counts.value.tous },
    { key: 'en_cours', label: 'En cours', count: counts.value.en_cours },
    { key: 'clos', label: 'Clos', count: counts.value.clos },
]);

const filteredList = computed(() => {
    if (filtre.value === 'tous') return litiges.value;
    if (filtre.value === 'en_cours') return litiges.value.filter(l => ['assignation','instruction','plaidoirie','en_attente'].includes(l.statut));
    return litiges.value.filter(l => ['gagné','perdu','transigé','classé'].includes(l.statut));
});

function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}
function formatCurrency(val) {
    return Number(val).toLocaleString('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 });
}

async function load() {
    error.value = null;
    try {
        const res = await fetch('/juridique/contentieux');
        if (!res.ok) throw new Error('Erreur ' + res.status);
        litiges.value = await res.json();
    } catch (e) {
        console.error(e);
        error.value = 'Impossible de charger les contentieux.';
    }
}
onMounted(load);
</script>
