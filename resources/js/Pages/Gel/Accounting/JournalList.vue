<script setup>
/*
 * JournalList.vue - Liste des écritures comptables / Journal (GEL)
 *
 * Affiche l'ensemble des écritures comptables d'un client avec
 * filtrage par période (date de début / date de fin).
 * Chaque écriture peut être validée (post) ou supprimée si elle
 * est encore à l'état "brouillon". Un clic sur une ligne affiche
 * le détail des lignes d'écriture (compte, débit, crédit).
 */
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

const journals = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedJournal = ref(null);

// Filtres de date pour la période d'affichage
const dateFrom = ref('');
const dateTo = ref('');

// Chargement des écritures avec filtres optionnels
const fetchJournals = async () => {
    loading.value = true;
    error.value = null;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) { error.value = 'Aucun client sélectionné.'; loading.value = false; return; }
    try {
        const params = new URLSearchParams();
        if (dateFrom.value) params.append('from', dateFrom.value);
        if (dateTo.value) params.append('to', dateTo.value);
        const url = '/api/accounting/journals/' + cid + (params.toString() ? '?' + params.toString() : '');
        const res = await fetch(url);
        if (!res.ok) throw new Error('Erreur de chargement');
        journals.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

// Validation (post) d'une écriture passant son statut à "valide"
const postJournal = async (id) => {
    if (!confirm('Confirmer la validation de cette écriture ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/accounting/journals/' + id + '/post', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de validation');
        await fetchJournals();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

// Suppression (soft delete) d'une écriture en brouillon
const deleteJournal = async (id) => {
    if (!confirm('Supprimer cette écriture ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/accounting/journals/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur');
        await fetchJournals();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

// Classe CSS du badge selon le statut de l'écriture
const statusBadge = (status) => {
    const map = { brouillon: 'bg-secondary', valide: 'bg-success', 'annule': 'bg-danger' };
    return map[status] || 'bg-secondary';
};

onMounted(fetchJournals);
</script>

<template>
    <GelLayout page-title="Journal Comptable">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <label class="small text-muted">Du:</label>
                <input v-model="dateFrom" type="date" class="form-control form-control-sm" style="width:140px;" @change="fetchJournals">
                <label class="small text-muted">Au:</label>
                <input v-model="dateTo" type="date" class="form-control form-control-sm" style="width:140px;" @change="fetchJournals">
            </div>
            <a :href="'/accounting/journals/create/' + clientId" class="btn btn-primary btn-sm">
                <i class="bi-plus-lg me-1"></i>Nouvelle écriture
            </a>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="bg-white rounded-lg shadow p-6">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Date</th>
                            <th>Libellé</th>
                            <th>Réf.</th>
                            <th>Statut</th>
                            <th class="text-end">Débit</th>
                            <th class="text-end">Crédit</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!journals.length">
                            <td colspan="7" class="text-center py-4 text-muted">Aucune écriture trouvée.</td>
                        </tr>
                        <tr v-for="j in journals" :key="j.id" @click="selectedJournal = selectedJournal?.id === j.id ? null : j" style="cursor:pointer;">
                            <td class="small">{{ $formatDate(j.date) }}</td>
                            <td class="fw-medium">{{ j.label }}</td>
                            <td class="small">{{ j.reference || '-' }}</td>
                            <td><span class="badge" :class="statusBadge(j.status)">{{ j.status }}</span></td>
                            <td class="text-end">{{ $formatCurrency(j.total_debit) }}</td>
                            <td class="text-end">{{ $formatCurrency(j.total_credit) }}</td>
                            <td class="text-end">
                                <button v-if="j.status === 'brouillon'" class="btn btn-sm btn-outline-success me-1" @click.stop="postJournal(j.id)" title="Valider"><i class="bi-check-lg"></i></button>
                                <button v-if="j.status === 'brouillon'" class="btn btn-sm btn-outline-danger" @click.stop="deleteJournal(j.id)" title="Supprimer"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Journal Lines Detail -->
        <div v-if="selectedJournal" class="bg-white rounded-lg shadow p-6 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-medium small">{{ selectedJournal.label }} - Lignes</span>
                <button class="btn btn-sm btn-light" @click="selectedJournal = null"><i class="bi-x"></i></button>
            </div>
            <table class="table table-sm mb-0">
                <thead class="small text-muted">
                    <tr><th>Compte</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th></tr>
                </thead>
                <tbody>
                    <tr v-for="line in selectedJournal.lines" :key="line.id">
                        <td class="small">{{ line.account?.account_number }} - {{ line.account?.name }}</td>
                        <td class="small">{{ line.label || '-' }}</td>
                        <td class="text-end small">{{ line.debit ? $formatCurrency(line.debit) : '-' }}</td>
                        <td class="text-end small">{{ line.credit ? $formatCurrency(line.credit) : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </GelLayout>
</template>
