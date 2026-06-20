<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">📁 Dossiers Juridiques</h4>
                <a href="/juridique/dossiers/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nouveau dossier
                </a>
            </div>

            <div class="row g-3">
                <div v-for="d in dossiers" :key="d.id" class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <code>{{ d.reference }}</code>
                                <span class="badge" :class="d.statut === 'clos' ? 'bg-secondary' : d.statut === 'en_cours' ? 'bg-primary' : d.statut === 'archive' ? 'bg-dark' : 'bg-info'">{{ d.statut }}</span>
                            </div>
                            <h6 class="fw-semibold">{{ d.titre }}</h6>
                            <div class="small text-muted mb-2">{{ d.type }} — {{ d.priorite }}</div>
                            <p class="small text-muted mb-0">{{ d.description ? d.description.substring(0, 100) + '...' : '—' }}</p>
                            <div class="mt-2 d-flex justify-content-between small">
                                <span><i class="bi bi-person me-1"></i>{{ d.assigned_to || 'Non assigné' }}</span>
                                <span><i class="bi bi-file-earmark me-1"></i>{{ d.documents?.length || 0 }} doc(s)</span>
                            </div>
                            <a :href="'/juridique/dossiers/' + d.id" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div v-if="!dossiers.length" class="col-12 text-center text-muted py-4">Aucun dossier</div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const dossiers = ref([]);

async function load() {
    try { const res = await fetch('/juridique/dossiers'); dossiers.value = await res.json(); }
    catch (e) { console.error(e); }
}
onMounted(load);
</script>
