<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div v-if="dossier" class="row g-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1">{{ dossier.titre }}</h4>
                            <code>{{ dossier.reference }}</code>
                        </div>
                        <span class="badge" :class="dossier.statut === 'clos' ? 'bg-secondary' : dossier.statut === 'en_cours' ? 'bg-primary' : 'bg-info'">{{ dossier.statut }}</span>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted">Type</small>
                                    <div class="fw-semibold">{{ dossier.type }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Priorité</small>
                                    <div>
                                        <span class="badge" :class="dossier.priorite === 'haute' ? 'bg-danger' : dossier.priorite === 'moyenne' ? 'bg-warning text-dark' : 'bg-success'">{{ dossier.priorite }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Assigné à</small>
                                    <div class="fw-semibold">{{ dossier.assigned_to || '—' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Description</small>
                                    <p class="mb-0">{{ dossier.description || '—' }}</p>
                                </div>
                            </div>

                            <div v-if="dossier.documents?.length" class="mt-4">
                                <h6 class="fw-semibold">Documents</h6>
                                <div v-for="(d, i) in dossier.documents" :key="i" class="d-flex align-items-center gap-2 border-bottom py-2">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span class="small">{{ d.nom || d }}</span>
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
                                <button class="btn btn-outline-primary btn-sm" @click="changerStatut">
                                    <i class="bi bi-arrow-repeat me-1"></i> Changer statut
                                </button>
                                <button v-if="dossier.statut !== 'clos'" class="btn btn-outline-danger btn-sm" @click="clore">
                                    <i class="bi bi-x-circle me-1"></i> Clore le dossier
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

const dossier = ref(null);
const id = window.location.pathname.split('/').pop();

async function load() {
    try { const res = await fetch('/juridique/dossiers/' + id); dossier.value = await res.json(); }
    catch (e) { console.error(e); }
}

async function changerStatut() {
    const nouveau = prompt('Nouveau statut (ouvert, en_cours, suspendu, clos, archive) :');
    if (!nouveau) return;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/dossiers/${id}/statut`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ statut: nouveau }),
        });
        if (res.ok) load();
    } catch (e) { console.error(e); }
}

async function clore() {
    if (!confirm('Clore ce dossier ?')) return;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/dossiers/${id}/statut`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ statut: 'clos' }),
        });
        if (res.ok) load();
    } catch (e) { console.error(e); }
}

onMounted(load);
</script>
