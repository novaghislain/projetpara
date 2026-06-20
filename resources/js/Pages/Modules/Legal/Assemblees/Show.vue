<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div v-if="ag" class="row g-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">{{ ag.type }} — {{ formatDate(ag.date_tenue) }}</h4>
                        <ContratStatusBadge :statut="ag.statut" :label="ag.statut" />
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <ul class="nav nav-tabs card-header-tabs">
                                <li class="nav-item"><a class="nav-link active" href="#">Informations</a></li>
                                <li class="nav-item"><a class="nav-link" href="#">Présences</a></li>
                                <li class="nav-item"><a class="nav-link" href="#">Résolutions</a></li>
                                <li class="nav-item"><a class="nav-link" href="#">PV</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted">Type</small>
                                    <div class="fw-semibold">{{ ag.type }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Date</small>
                                    <div class="fw-semibold">{{ formatDate(ag.date_tenue) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Lieu</small>
                                    <div class="fw-semibold">{{ ag.lieu }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Quorum requis</small>
                                    <div class="fw-semibold">{{ ag.quorum_requis ?? '—' }}%</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Quorum atteint</small>
                                    <div class="fw-semibold">{{ ag.quorum_atteint ?? '—' }}%</div>
                                </div>
                            </div>

                            <hr />
                            <h6 class="fw-semibold">Ordre du jour</h6>
                            <ol>
                                <li v-for="(point, i) in ag.ordre_du_jour" :key="i">{{ typeof point === 'string' ? point : point.titre }}</li>
                            </ol>

                            <div v-if="ag.resolutions?.length" class="mt-3">
                                <h6 class="fw-semibold">Résolutions</h6>
                                <div v-for="(res, i) in ag.resolutions" :key="i" class="border rounded p-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span><strong>{{ res.numero || 'Résolution ' + (i+1) }}</strong> — {{ res.intitule }}</span>
                                        <span class="badge bg-success">{{ res.votes_pour || 0 }} pour</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-semibold">Actions</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary btn-sm" @click="genererConvocation">
                                    <i class="bi bi-envelope me-1"></i> Générer convocation
                                </button>
                                <button class="btn btn-outline-success btn-sm" @click="genererPV">
                                    <i class="bi bi-file-pdf me-1"></i> Générer PV
                                </button>
                                <button v-if="!ag.pv_approuve" class="btn btn-outline-primary btn-sm" @click="approuverPV">
                                    <i class="bi bi-check-lg me-1"></i> Approuver PV
                                </button>
                            </div>
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
import ContratStatusBadge from '../../../../Components/Legal/ContratStatusBadge.vue';

const ag = ref(null);
const agId = window.location.pathname.split('/').pop();

function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}

async function loadAG() {
    try {
        const res = await fetch(`/juridique/assemblees/${agId}`);
        ag.value = await res.json();
    } catch (e) { console.error(e); }
}

async function genererConvocation() {
    try {
        const res = await fetch(`/juridique/assemblees/${agId}/convocation`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content } });
        if (res.ok) alert('Convocation générée');
    } catch (e) { console.error(e); }
}

async function genererPV() {
    try {
        const res = await fetch(`/juridique/assemblees/${agId}/pv`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content } });
        if (res.ok) { alert('PV généré'); ag.value.pv_approuve = true; }
    } catch (e) { console.error(e); }
}

async function approuverPV() {
    try {
        const res = await fetch(`/juridique/assemblees/${agId}/approuver-pv`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content } });
        if (res.ok) ag.value.pv_approuve = true;
    } catch (e) { console.error(e); }
}

onMounted(loadAG);
</script>
