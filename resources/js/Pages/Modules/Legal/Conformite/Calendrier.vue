<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">📅 Calendrier de Conformité</h4>
                <a href="/juridique/conformite" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Retour
                </a>
            </div>

            <div class="row g-3">
                <div v-for="(items, mois) in groupedByMonth" :key="mois" class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-semibold">
                            {{ mois }}
                        </div>
                        <div class="card-body p-0">
                            <div v-for="c in items" :key="c.id" class="border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold small">{{ c.intitule }}</div>
                                    <div class="small text-muted">{{ c.organisme }} — {{ c.type }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="small" :class="isOverdue(c.date_echeance) ? 'text-danger' : ''">{{ formatDate(c.date_echeance) }}</div>
                                    <span class="badge" :class="c.statut === 'conforme' ? 'bg-success' : c.statut === 'en_retard' ? 'bg-danger' : 'bg-warning text-dark'">{{ c.statut }}</span>
                                </div>
                            </div>
                            <div v-if="!items.length" class="p-3 text-muted small">Aucune échéance</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const conformites = ref([]);

const groupedByMonth = computed(() => {
    const groups = {};
    const sorted = [...conformites.value].sort((a, b) => (a.date_echeance || '').localeCompare(b.date_echeance || ''));
    for (const c of sorted) {
        if (!c.date_echeance) continue;
        const d = new Date(c.date_echeance + 'T00:00:00');
        const key = d.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
        if (!groups[key]) groups[key] = [];
        groups[key].push(c);
    }
    return groups;
});

function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}
function isOverdue(date) {
    if (!date) return false;
    return new Date(date + 'T00:00:00') < new Date();
}

async function load() {
    try { const res = await fetch('/juridique/conformite'); conformites.value = await res.json(); }
    catch (e) { console.error(e); }
}
onMounted(load);
</script>
