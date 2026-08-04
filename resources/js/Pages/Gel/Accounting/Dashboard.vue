<script setup>
/*
 * Dashboard.vue - Tableau de bord comptable principal (GEL)
 *
 * Affiche les statistiques globales (clients, écritures, comptes, alertes)
 * ainsi que la liste des clients avec leurs statuts.
 * Permet d'accéder au plan comptable, aux journaux, à la balance
 * ou de basculer vers l'espace client complet.
 */
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const clients = ref([]);
const stats = ref(null);
const loading = ref(true);
const error = ref(null);

// Chargement des données au montage du composant
const fetchData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const [clientsRes, statsRes] = await Promise.all([
            fetch('/api/clients'),
            fetch('/api/stats'),
        ]);
        if (!clientsRes.ok) throw new Error('Erreur chargement clients');
        clients.value = await clientsRes.json();
        if (statsRes.ok) stats.value = await statsRes.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);

// Classe CSS du badge de statut client
const statusBadge = (s) => ({
    actif: 'badge bg-success',
    inactif: 'badge bg-secondary',
    suspendu: 'badge bg-warning text-dark',
}[s] || 'badge bg-light text-dark');
// Bascule vers l'espace comptable complet du client sélectionné
const switchAndGo = async (clientId) => {
    try {
        await authStore.switchToCompany(clientId);
        window.location.href = '/company/accounting';
    } catch (e) {
        alert("Erreur lors de l'accès au dossier client.");
    }
};
</script>

<template>
    <GelLayout page-title="Comptabilité">
        <div v-if="loading" class="d-flex align-items-center justify-content-center py-5 gap-3">
            <div class="spinner-border text-primary" role="status"></div>
            <span style="color:#888; font-size:14px;">Chargement…</span>
        </div>

        <div v-else-if="error" class="alert alert-danger">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
            <button @click="fetchData" class="btn btn-outline-danger btn-sm ms-3">Réessayer</button>
        </div>

        <template v-else>
            <!-- Stats -->
            <div class="row g-3 mb-4" v-if="stats">
                <div class="col-md-3 col-6">
                    <div class="bg-white rounded-lg shadow p-6 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#fff3e0;color:#FF7900;">
                                <i class="bi-building"></i>
                            </div>
                            <div>
                                <div class="fs-5 fw-bold">{{ stats.total_clients || 0 }}</div>
                                <div class="text-muted small">Clients comptables</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white rounded-lg shadow p-6 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32;">
                                <i class="bi-journal"></i>
                            </div>
                            <div>
                                <div class="fs-5 fw-bold">{{ stats.total_journals || 0 }}</div>
                                <div class="text-muted small">Écritures du mois</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white rounded-lg shadow p-6 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#e3f2fd;color:#1565c0;">
                                <i class="bi-calculator"></i>
                            </div>
                            <div>
                                <div class="fs-5 fw-bold">{{ stats.accounts_count || 0 }}</div>
                                <div class="text-muted small">Comptes actifs</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white rounded-lg shadow p-6 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#fce4ec;color:#c62828;">
                                <i class="bi-exclamation-triangle"></i>
                            </div>
                            <div>
                                <div class="fs-5 fw-bold">{{ stats.alerts_count || 0 }}</div>
                                <div class="text-muted small">Alertes comptables</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des clients -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi-building me-2"></i>Clients — Comptabilité</span>
                    <span class="badge bg-light text-dark">{{ clients.length }} client(s)</span>
                </div>
                <div class="card-body p-0">
                    <div v-if="clients.length === 0" class="text-center py-4 text-muted">
                        <i class="bi-folder2-open" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                        Aucun client trouvé
                    </div>
                    <table v-else class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Société</th>
                                <th>Statut</th>
                                <th>Pôle</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in clients" :key="c.id">
                                <td>
                                    <strong>{{ c.company_name || '—' }}</strong>
                                    <div class="text-muted small">{{ c.email }}</div>
                                </td>
                                <td>
                                    <span :class="statusBadge(c.statut)">{{ c.statut || 'actif' }}</span>
                                </td>
                                <td>{{ c.poles?.length ? c.poles.map(p => p.name || p.nom).join(', ') : '—' }}</td>
                                <td class="text-end">
                                    <a :href="'/accounting/accounts/' + c.id" class="btn btn-sm btn-outline-primary me-1" title="Plan comptable">
                                        <i class="bi-journal-text"></i>
                                    </a>
                                    <a :href="'/accounting/journals/' + c.id" class="btn btn-sm btn-outline-primary me-1" title="Journaux">
                                        <i class="bi-pencil-square"></i>
                                    </a>
                                    <a :href="'/accounting/reports/balance/' + c.id" class="btn btn-sm btn-outline-primary" title="Balance">
                                        <i class="bi-file-earmark-bar-graph"></i>
                                    </a>
                                    <button @click="switchAndGo(c.id)" class="btn btn-sm btn-outline-success ms-1" title="Accès complet (Budgets, Clôture...)">
                                        <i class="bi-box-arrow-in-right"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </GelLayout>
</template>
