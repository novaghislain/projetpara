<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div v-if="registre" class="row g-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1">{{ typeLabel(registre.type) }} {{ registre.annee }}</h4>
                            <code v-if="registre.is_closed" class="text-muted">Clos le {{ formatDate(registre.closed_at) }}</code>
                        </div>
                        <span class="badge" :class="registre.is_closed ? 'bg-secondary' : 'bg-success'">{{ registre.is_closed ? 'Clos' : 'Ouvert' }}</span>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Entrées ({{ registre.entrees?.length || 0 }})</span>
                            <button v-if="!registre.is_closed" class="btn btn-primary btn-sm" @click="showAddForm = true">
                                <i class="bi bi-plus-lg me-1"></i> Ajouter entrée
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>N°</th>
                                        <th>Date</th>
                                        <th>Intitulé</th>
                                        <th>Référence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(e, i) in registre.entrees" :key="i">
                                        <td>{{ i + 1 }}</td>
                                        <td>{{ e.date || '—' }}</td>
                                        <td>{{ e.intitule || e.titre || '—' }}</td>
                                        <td><code>{{ e.reference || '—' }}</code></td>
                                    </tr>
                                    <tr v-if="!registre.entrees?.length">
                                        <td colspan="4" class="text-center text-muted py-4">Aucune entrée</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-semibold">Informations</h6>
                            <div class="mb-2">
                                <small class="text-muted">Type</small>
                                <div class="fw-semibold">{{ typeLabel(registre.type) }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Année</small>
                                <div class="fw-semibold">{{ registre.annee }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Statut</small>
                                <div>
                                    <span class="badge" :class="registre.is_closed ? 'bg-secondary' : 'bg-success'">{{ registre.is_closed ? 'Clos' : 'Ouvert' }}</span>
                                </div>
                            </div>
                            <div v-if="!registre.is_closed" class="mt-3">
                                <button class="btn btn-outline-danger btn-sm w-100" @click="cloreRegistre">
                                    <i class="bi bi-lock me-1"></i> Clore le registre
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal add entry -->
            <div v-if="showAddForm" class="modal d-block" style="background:rgba(0,0,0,0.4)">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header">
                            <h6 class="fw-semibold mb-0">Ajouter une entrée</h6>
                            <button type="button" class="btn-close" @click="showAddForm = false"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Date</label>
                                <input v-model="newEntry.date" type="date" class="form-control form-control-sm" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Intitulé</label>
                                <input v-model="newEntry.intitule" class="form-control form-control-sm" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Référence</label>
                                <input v-model="newEntry.reference" class="form-control form-control-sm" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary btn-sm" @click="showAddForm = false">Annuler</button>
                            <button class="btn btn-primary btn-sm" @click="addEntry">Ajouter</button>
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

const registre = ref(null);
const showAddForm = ref(false);
const newEntry = ref({ date: '', intitule: '', reference: '' });

const parts = window.location.pathname.split('/').filter(Boolean);
const registreType = parts[parts.length - 2] || 'registre_assemblee';
const annee = parts[parts.length - 1] || new Date().getFullYear();

const typeLabels = {
    deliberations: 'Délibérations', decisions: 'Décisions', actions: 'Actions',
    contrats: 'Contrats', courriers: 'Courriers', comptes_annuels: 'Comptes annuels',
    divers: 'Divers',
};

function typeLabel(type) { return typeLabels[type] || type; }

function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}

async function load() {
    try { const res = await fetch(`/juridique/registres/${registreType}?annee=${annee}`); registre.value = await res.json(); }
    catch (e) { console.error(e); }
}

async function addEntry() {
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/registres/${registreType}/${annee}/entree`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ objet: newEntry.value.intitule, details: newEntry.value.reference }),
        });
        if (res.ok) { showAddForm.value = false; newEntry.value = { date: '', intitule: '', reference: '' }; load(); }
    } catch (e) { console.error(e); }
}

async function cloreRegistre() {
    if (!confirm('Clore ce registre ? Cette action est irréversible.')) return;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/registres/${registreType}/${annee}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ is_closed: true }),
        });
        if (res.ok) load();
    } catch (e) { console.error(e); }
}

onMounted(load);
</script>
