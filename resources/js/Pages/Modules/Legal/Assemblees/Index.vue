<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">📋 Assemblées Générales</h4>
                <a href="/juridique/assemblees/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Planifier une AG
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Lieu</th>
                                    <th>Statut</th>
                                    <th>PV</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ag in assemblees" :key="ag.id">
                                    <td><strong>{{ ag.type }}</strong></td>
                                    <td>{{ formatDate(ag.date_tenue) }}</td>
                                    <td>{{ ag.lieu }}</td>
                                    <td><ContratStatusBadge :statut="ag.statut" :label="ag.statut" /></td>
                                    <td>
                                        <span v-if="ag.pv_path" class="text-success"><i class="bi bi-check-circle"></i> Disponible</span>
                                        <span v-else class="text-muted">—</span>
                                    </td>
                                    <td>
                                        <a :href="'/juridique/assemblees/' + ag.id" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" @click="deleteAG(ag.id)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!assemblees.length">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Aucune assemblée générale
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import ContratStatusBadge from '../../../../Components/Legal/ContratStatusBadge.vue';

const assemblees = ref([]);

function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}

async function loadAG() {
    try {
        const res = await fetch('/juridique/assemblees');
        assemblees.value = await res.json();
    } catch (e) { console.error(e); }
}

async function deleteAG(id) {
    if (!confirm('Supprimer cette AG ?')) return;
    try {
        await fetch(`/juridique/assemblees/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content } });
        assemblees.value = assemblees.value.filter(a => a.id !== id);
    } catch (e) { console.error(e); }
}

onMounted(loadAG);
</script>
