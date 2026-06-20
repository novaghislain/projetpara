<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    tickets: Object,
    clients: Array,
});

// ── Filters ──
const search = ref('');
const filterClientId = ref('');
const filterStatus = ref('');
const filterPriority = ref('');
const ticketsData = ref(null);
const loading = ref(false);
let debounceTimer = null;

// ── Helpers ──
const priorityLabel = (p) =>
    ({ low: 'Basse', medium: 'Moyenne', high: 'Haute', critical: 'Critique' }[p] || p);

const priorityClass = (p) =>
    ({ low: 'bg-secondary', medium: 'bg-primary', high: 'bg-warning text-dark', critical: 'bg-danger' }[p] || 'bg-secondary');

const statusLabel = (s) =>
    ({ open: 'Ouvert', assigned: 'Assigné', in_progress: 'En cours', pending: 'En attente', resolved: 'Résolu', closed: 'Fermé' }[s] || s);

const statusClass = (s) =>
    ({ open: 'bg-primary', assigned: 'bg-info', in_progress: 'bg-warning text-dark', pending: 'bg-secondary', resolved: 'bg-success', closed: 'bg-dark' }[s] || 'bg-secondary');

const slaBadge = (dueAt) => {
    if (!dueAt) return 'bg-secondary';
    const hoursLeft = (new Date(dueAt) - new Date()) / (1000 * 60 * 60);
    if (hoursLeft < 0) return 'bg-danger';
    if (hoursLeft <= 4) return 'bg-warning text-dark';
    return 'bg-success';
};

const slaLabel = (dueAt) => {
    if (!dueAt) return 'N/A';
    const hoursLeft = (new Date(dueAt) - new Date()) / (1000 * 60 * 60);
    if (hoursLeft < 0) return 'Dépassé';
    if (hoursLeft <= 4) return 'Urgent';
    return 'OK';
};

const formatDateShort = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'short', year: 'numeric',
    });
};

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const hasTickets = computed(() => ticketsData.value?.data?.length > 0);

// ── Data fetching (SPA: JSON via Accept header) ──
const fetchTickets = async (page) => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        if (search.value) params.append('search', search.value);
        if (filterClientId.value) params.append('client_id', filterClientId.value);
        if (filterStatus.value) params.append('status', filterStatus.value);
        if (filterPriority.value) params.append('priority', filterPriority.value);
        if (page) params.append('page', page);

        const res = await fetch('/it/tickets?' + params.toString(), {
            headers: { Accept: 'application/json' },
        });
        if (res.ok) {
            ticketsData.value = await res.json();
        }
    } catch (e) {
        console.error('Erreur de chargement des tickets:', e);
    } finally {
        loading.value = false;
    }
};

// ── Watchers ──
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchTickets(), 350);
});
watch([filterClientId, filterStatus, filterPriority], () => fetchTickets());

// ── Pagination ──
const goToPage = (url) => {
    if (!url) return;
    const u = new URL(url, window.location.origin);
    fetchTickets(u.searchParams.get('page'));
};

// ── Lifecycle ──
onMounted(() => {
    ticketsData.value = props.tickets;
});
</script>

<template>
    <GelLayout page-title="Tickets IT">
        <div>
            <!-- ══ HEADER ══ -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-0" style="color:#163A5E;">Tickets de support</h4>
                    <p class="text-muted small mb-0">Gestion des incidents, demandes et changements IT</p>
                </div>
                <a href="/it/tickets/create" class="btn btn-primary btn-sm flex-shrink-0">
                    <i class="bi-plus-lg me-1"></i>Nouveau ticket
                </a>
            </div>

            <!-- ══ FILTRES ══ -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <div class="input-group input-group-sm" style="max-width:280px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" type="text" class="form-control" placeholder="Rechercher titre ou n° ticket...">
                </div>
                <select v-model="filterClientId" class="form-select form-select-sm" style="width:auto; min-width:160px;">
                    <option value="">Tous les clients</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                </select>
                <select v-model="filterStatus" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="open">Ouvert</option>
                    <option value="assigned">Assigné</option>
                    <option value="in_progress">En cours</option>
                    <option value="pending">En attente</option>
                    <option value="resolved">Résolu</option>
                    <option value="closed">Fermé</option>
                </select>
                <select v-model="filterPriority" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Toutes priorités</option>
                    <option value="low">Basse</option>
                    <option value="medium">Moyenne</option>
                    <option value="high">Haute</option>
                    <option value="critical">Critique</option>
                </select>
                <span v-if="ticketsData" class="small text-muted ms-1">
                    {{ ticketsData.total }} ticket(s)
                </span>
            </div>

            <!-- ══ LOADING ══ -->
            <div v-if="loading" class="d-flex justify-content-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <!-- ══ TABLEAU ══ -->
            <div v-else class="bg-white rounded-lg shadow p-6">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-muted">
                            <tr>
                                <th>Ticket#</th>
                                <th>Titre</th>
                                <th>Client</th>
                                <th>Assigné à</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>SLA</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!hasTickets">
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi-ticket" style="font-size:32px; color:#dce3ee; display:block; margin-bottom:8px;"></i>
                                    Aucun ticket trouvé.
                                </td>
                            </tr>
                            <tr v-for="ticket in ticketsData?.data" :key="ticket.id">
                                <td>
                                    <a :href="'/it/tickets/' + ticket.id"
                                       class="text-decoration-none fw-medium"
                                       style="font-family:'SF Mono','Fira Code','Consolas',monospace; font-size:12px;">
                                        {{ ticket.ticket_number }}
                                    </a>
                                </td>
                                <td style="max-width:260px;">
                                    <div class="text-truncate fw-medium" :title="ticket.title">
                                        {{ ticket.title }}
                                    </div>
                                    <div class="small text-muted">{{ formatDate(ticket.created_at) }}</div>
                                </td>
                                <td class="small">{{ ticket.client?.company_name || '-' }}</td>
                                <td class="small">{{ ticket.assigned_to?.name || 'Non assigné' }}</td>
                                <td>
                                    <span class="badge" :class="priorityClass(ticket.priority)">
                                        {{ priorityLabel(ticket.priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="statusClass(ticket.status)">
                                        {{ statusLabel(ticket.status) }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="ticket.sla_due_at" class="badge" :class="slaBadge(ticket.sla_due_at)">
                                        {{ slaLabel(ticket.sla_due_at) }}
                                    </span>
                                    <span v-else class="badge bg-secondary">N/A</span>
                                    <div v-if="ticket.sla_due_at" class="small text-muted" style="font-size:10px;">
                                        {{ formatDateShort(ticket.sla_due_at) }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a :href="'/it/tickets/' + ticket.id"
                                       class="btn btn-sm btn-outline-secondary" title="Voir le détail">
                                        <i class="bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ══ PAGINATION ══ -->
                <div v-if="ticketsData?.last_page > 1"
                     class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">
                        Page {{ ticketsData.current_page }} / {{ ticketsData.last_page }}
                        ({{ ticketsData.total }} résultat{{ ticketsData.total > 1 ? 's' : '' }})
                    </small>
                    <nav aria-label="Pagination des tickets">
                        <ul class="pagination pagination-sm mb-0">
                            <li v-for="(link, i) in ticketsData.links" :key="i"
                                class="page-item"
                                :class="{ disabled: !link.url, active: link.active }">
                                <button class="page-link"
                                        @click="goToPage(link.url)"
                                        :disabled="!link.url || link.active"
                                        v-html="link.label">
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
