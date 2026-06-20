<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const expenses = ref([]);
const loading = ref(true);
const error = ref(null);
const search = ref('');
const statusFilter = ref('');

const fetchExpenses = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/rh/expenses');
        if (!res.ok) throw new Error('Erreur de chargement');
        expenses.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredExpenses = computed(() => {
    let list = expenses.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(e =>
            (e.employe_nom && e.employe_nom.toLowerCase().includes(q)) ||
            (e.categorie && e.categorie.toLowerCase().includes(q))
        );
    }
    if (statusFilter.value) {
        list = list.filter(e => e.statut === statusFilter.value);
    }
    return list;
});

const statusBadgeClass = (status) => {
    const map = {
        en_attente: 'bg-warning text-dark',
        approuve: 'bg-success',
        rejete: 'bg-danger',
        rembourse: 'bg-info',
    };
    return map[status] || 'bg-secondary';
};

const updateStatus = async (id, newStatus) => {
    const actionLabel = newStatus === 'approuve' ? 'approuver' : 'rejeter';
    if (!confirm('Confirmer la ' + actionLabel + ' de cette note de frais ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/expenses/' + id + '/status', {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ statut: newStatus }),
        });
        if (!res.ok) throw new Error('Erreur de mise à jour');
        await fetchExpenses();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const deleteExpense = async (id) => {
    if (!confirm('Confirmer la suppression de cette note de frais ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/expenses/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de suppression');
        await fetchExpenses();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' FCFA';
};

onMounted(fetchExpenses);
</script>

<template>
    <GelLayout page-title="Notes de Frais">
        <!-- Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:260px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" class="form-control form-control-sm" placeholder="Rechercher...">
                </div>
                <select v-model="statusFilter" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="approuve">Approuvé</option>
                    <option value="rejete">Rejeté</option>
                    <option value="rembourse">Remboursé</option>
                </select>
            </div>
            <a href="/rh/expenses/create" class="btn btn-primary btn-sm">
                <i class="bi-plus-lg me-1"></i>Nouvelle note
            </a>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Stats -->
        <div v-else class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Total montant</div>
                        <div class="fw-bold fs-6">{{ formatCurrency(expenses.reduce((s, e) => s + (parseFloat(e.montant) || 0), 0)) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">En attente</div>
                        <div class="fw-bold fs-5 text-warning">{{ expenses.filter(e => e.statut === 'en_attente').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Approuvés</div>
                        <div class="fw-bold fs-5 text-success">{{ expenses.filter(e => e.statut === 'approuve').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Rejetés</div>
                        <div class="fw-bold fs-5 text-danger">{{ expenses.filter(e => e.statut === 'rejete').length }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div v-if="!loading && !error" class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Employé</th>
                            <th>Catégorie</th>
                            <th class="text-end">Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredExpenses.length">
                            <td colspan="6" class="text-center py-4 text-muted">Aucune note de frais trouvée.</td>
                        </tr>
                        <tr v-for="e in filteredExpenses" :key="e.id">
                            <td class="fw-medium">{{ e.employe_nom || e.employe?.nom }} {{ e.employe_prenom || e.employe?.prenom }}</td>
                            <td><span class="badge bg-secondary">{{ e.categorie }}</span></td>
                            <td class="text-end fw-medium">{{ formatCurrency(e.montant) }}</td>
                            <td><span class="badge" :class="statusBadgeClass(e.statut)">{{ e.statut }}</span></td>
                            <td class="small">{{ $formatDate ? $formatDate(e.date) : e.date }}</td>
                            <td class="text-end">
                                <button v-if="e.statut === 'en_attente'" class="btn btn-sm btn-outline-success me-1" @click="updateStatus(e.id, 'approuve')" title="Approuver"><i class="bi-check-lg"></i></button>
                                <button v-if="e.statut === 'en_attente'" class="btn btn-sm btn-outline-danger me-1" @click="updateStatus(e.id, 'rejete')" title="Rejeter"><i class="bi-x-lg"></i></button>
                                <a :href="'/rh/expenses/' + e.id + '/edit'" class="btn btn-sm btn-outline-primary me-1" title="Modifier"><i class="bi-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteExpense(e.id)" title="Supprimer"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">
                <span>{{ filteredExpenses.length }} note(s) sur {{ expenses.length }}</span>
            </div>
        </div>
    </GelLayout>
</template>
