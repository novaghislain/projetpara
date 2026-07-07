<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';
import DataTable from '../../../Components/Accounting/DataTable.vue';
import DateInput from '../../../Components/Accounting/DateInput.vue';
import PeriodSelector from '../../../Components/Accounting/PeriodSelector.vue';

const entries = ref([]);
const journals = ref([]);
const pagination = ref(null);
const showCreateModal = ref(false);
const filters = ref({
    journal_id: '',
    date_from: '',
    date_to: '',
    status: '',
});
const selectedEntry = ref(null);
const showDetailModal = ref(false);

const columns = [
    { key: 'entry_date', label: 'Date', type: 'date', sortable: true },
    { key: 'reference', label: 'Référence', sortable: true },
    { key: 'journal_code', label: 'Journal' },
    { key: 'description', label: 'Libellé' },
    { key: 'total_debit', label: 'Débit', type: 'money', sortable: true },
    { key: 'total_credit', label: 'Crédit', type: 'money', sortable: true },
    { key: 'status', label: 'Statut', type: 'status' },
];

async function loadEntries(page = 1) {
    try {
        const params = new URLSearchParams({ page, ...filters.value });
        const res = await fetch(`/api/entries?${params}`, { headers: { Accept: 'application/json' } });
        const result = await res.json();
        entries.value = result.data?.data || [];
        pagination.value = result.data?.meta || null;
    } catch (e) {
        console.error('Erreur chargement écritures:', e);
    }
}

async function loadJournals() {
    try {
        const res = await fetch('/api/journals', { headers: { Accept: 'application/json' } });
        const result = await res.json();
        journals.value = result.data || [];
    } catch (e) { console.error('Erreur chargement journaux:', e); }
}

function viewEntry(entry) {
    selectedEntry.value = entry;
    showDetailModal.value = true;
}

async function postEntry(id) {
    if (!confirm('Valider cette écriture ?')) return;
    try {
        await fetch(`/api/entries/${id}/post`, { method: 'POST', headers: { Accept: 'application/json' } });
        await loadEntries();
    } catch (e) { console.error('Erreur validation:', e); }
}

async function cancelEntry(id) {
    if (!confirm('Annuler cette écriture ?')) return;
    try {
        await fetch(`/api/entries/${id}/cancel`, { method: 'POST', headers: { Accept: 'application/json' } });
        await loadEntries();
    } catch (e) { console.error('Erreur annulation:', e); }
}

onMounted(() => { loadEntries(); loadJournals(); });
</script>

<template>
    <CompanyLayout page-title="Journal des Écritures">
        <template #header-actions>
            <button @click="showCreateModal = true" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle écriture
            </button>
        </template>

        <!-- Filtres -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Journal</label>
                        <select v-model="filters.journal_id" class="form-select form-select-sm">
                            <option value="">Tous</option>
                            <option v-for="j in journals" :key="j.id" :value="j.id">{{ j.code }} — {{ j.label }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <DateInput v-model="filters.date_from" label="Du" />
                    </div>
                    <div class="col-md-2">
                        <DateInput v-model="filters.date_to" label="Au" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Statut</label>
                        <select v-model="filters.status" class="form-select form-select-sm">
                            <option value="">Tous</option>
                            <option value="draft">Brouillon</option>
                            <option value="posted">Validée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button @click="loadEntries()" class="btn btn-outline-primary btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>Filtrer
                        </button>
                        <button @click="Object.assign(filters, { journal_id: '', date_from: '', date_to: '', status: '' }); loadEntries()"
                                class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <DataTable
            :columns="columns"
            :data="entries"
            :pagination="pagination"
            @page-change="loadEntries"
            @row-click="viewEntry">
            <template #actions="{ row }">
                <button @click.stop="viewEntry(row)" class="btn btn-sm btn-outline-primary me-1" title="Voir">
                    <i class="bi bi-eye"></i>
                </button>
                <button v-if="row.status === 'draft'" @click.stop="postEntry(row.id)"
                        class="btn btn-sm btn-outline-success me-1" title="Valider">
                    <i class="bi bi-check-lg"></i>
                </button>
                <button v-if="row.status === 'draft'" @click.stop="cancelEntry(row.id)"
                        class="btn btn-sm btn-outline-danger" title="Annuler">
                    <i class="bi bi-x-lg"></i>
                </button>
            </template>
        </DataTable>

        <!-- Modal Détail -->
        <div v-if="showDetailModal && selectedEntry" class="modal-backdrop fade show" @click="showDetailModal = false"></div>
        <div v-if="showDetailModal && selectedEntry" class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Écriture {{ selectedEntry.reference }}</h5>
                        <button type="button" class="btn-close" @click="showDetailModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-3">
                                <small class="text-muted">Date</small>
                                <p class="fw-medium mb-0">{{ selectedEntry.entry_date }}</p>
                            </div>
                            <div class="col-3">
                                <small class="text-muted">Référence</small>
                                <p class="fw-medium font-mono mb-0">{{ selectedEntry.reference }}</p>
                            </div>
                            <div class="col-3">
                                <small class="text-muted">Journal</small>
                                <p class="fw-medium mb-0">{{ selectedEntry.journal_code }}</p>
                            </div>
                            <div class="col-3">
                                <small class="text-muted">Statut</small>
                                <p class="mb-0"><span :class="getStatusBadge(selectedEntry.status)">{{ selectedEntry.status }}</span></p>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Libellé</small>
                                <p class="mb-0">{{ selectedEntry.description }}</p>
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-2">Lignes d'écriture</h6>
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Compte</th>
                                    <th class="small">Libellé</th>
                                    <th class="small text-end">Débit</th>
                                    <th class="small text-end">Crédit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="line in (selectedEntry.lines || selectedEntry.entry_lines || [])" :key="line.id">
                                    <td class="font-mono small">{{ line.account_code || line.code }}</td>
                                    <td class="small">{{ line.description || line.label }}</td>
                                    <td class="text-end small fw-medium">{{ formatMoney(line.debit) }}</td>
                                    <td class="text-end small fw-medium">{{ formatMoney(line.credit) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="small">TOTAUX</th>
                                    <th class="text-end small">{{ formatMoney(selectedEntry.total_debit) }}</th>
                                    <th class="text-end small">{{ formatMoney(selectedEntry.total_credit) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button v-if="selectedEntry.status === 'draft'" @click="postEntry(selectedEntry.id); showDetailModal = false"
                                class="btn btn-success btn-sm">
                            <i class="bi bi-check-lg me-1"></i>Valider
                        </button>
                        <button @click="showDetailModal = false" class="btn btn-outline-secondary btn-sm">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Création -->
        <div v-if="showCreateModal" class="modal-backdrop fade show" @click="showCreateModal = false"></div>
        <div v-if="showCreateModal" class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouvelle écriture comptable</h5>
                        <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <div class="modal-body p-4">
                        <CreateEntryForm @saved="showCreateModal = false; loadEntries()" @cancel="showCreateModal = false" />
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
.font-mono { font-family: 'Courier New', monospace; }
</style>
