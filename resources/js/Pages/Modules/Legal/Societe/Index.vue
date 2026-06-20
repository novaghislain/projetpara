<template>
    <GelLayout>
        <div class="legal-societe p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">🏢 Fiche d'identité de la société</h4>
                <div>
                    <button v-if="!editing" class="btn btn-outline-primary btn-sm" @click="editing = true">
                        <i class="bi bi-pencil me-1"></i> Modifier
                    </button>
                    <button v-else class="btn btn-success btn-sm" @click="save">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer
                    </button>
                    <button v-if="editing" class="btn btn-secondary btn-sm ms-2" @click="cancelEdit">
                        Annuler
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Raison sociale</label>
                            <input v-model="form.raison_sociale" class="form-control" :disabled="!editing" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Forme juridique</label>
                            <select v-model="form.forme_juridique" class="form-select" :disabled="!editing">
                                <option value="SARL">SARL</option>
                                <option value="SA">SA</option>
                                <option value="SAS">SAS</option>
                                <option value="EURL">EURL</option>
                                <option value="SNC">SNC</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Capital social (FCFA)</label>
                            <input v-model="form.capital_social" type="number" class="form-control" :disabled="!editing" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">RCCM</label>
                            <input v-model="form.numero_rccm" class="form-control" :disabled="!editing" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">IFU</label>
                            <input v-model="form.ifu" class="form-control" :disabled="!editing" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date de création</label>
                            <input v-model="form.date_creation" type="date" class="form-control" :disabled="!editing" />
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Siège social</label>
                            <textarea v-model="form.siege_social" class="form-control" rows="2" :disabled="!editing"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Objet social</label>
                            <textarea v-model="form.objet_social" class="form-control" rows="3" :disabled="!editing"></textarea>
                        </div>
                    </div>

                    <hr />
                    <h6 class="fw-semibold mb-3">Gérant / Dirigeant</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nom</label>
                            <input v-model="form.gerant_nom" class="form-control" :disabled="!editing" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prénom</label>
                            <input v-model="form.gerant_prenom" class="form-control" :disabled="!editing" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nationalité</label>
                            <input v-model="form.gerant_nationalite" class="form-control" :disabled="!editing" />
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!editing" class="text-muted small mt-2 text-end">
                Dernière modification : {{ form.updated_at ? formatDate(form.updated_at) : '—' }}
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const editing = ref(false);
const form = ref({
    raison_sociale: '',
    forme_juridique: 'SARL',
    capital_social: null,
    numero_rccm: '',
    ifu: '',
    date_creation: '',
    siege_social: '',
    objet_social: '',
    gerant_nom: '',
    gerant_prenom: '',
    gerant_nationalite: '',
});
const original = ref(null);

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR');
}

async function loadSociete() {
    try {
        const res = await fetch('/juridique/societe');
        if (res.ok) {
            const data = await res.json();
            if (data) {
                form.value = { ...form.value, ...data };
                original.value = JSON.parse(JSON.stringify(form.value));
            }
        }
    } catch (e) {
        console.error('Erreur chargement société', e);
    }
}

async function save() {
    try {
        const res = await fetch('/juridique/societe', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(form.value),
        });
        if (res.ok) {
            editing.value = false;
            original.value = JSON.parse(JSON.stringify(form.value));
        }
    } catch (e) {
        console.error('Erreur sauvegarde', e);
    }
}

function cancelEdit() {
    form.value = JSON.parse(JSON.stringify(original.value));
    editing.value = false;
}

onMounted(loadSociete);
</script>

<style scoped>
.legal-societe { min-height: 100vh; background: #f8f9fa; }
</style>
