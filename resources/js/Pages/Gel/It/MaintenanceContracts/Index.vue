<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    contracts: Object,
    clients: Array,
});

// ── Filters ──
const filterClientId = ref('');
const filterStatus = ref('');
const contractsData = ref(null);
const loading = ref(false);
let debounceTimer = null;

// ── Helpers ──
const typeLabel = (t) =>
    ({ corrective: 'Corrective', preventive: 'Préventive', full_service: 'Service complet', hotline: 'Hotline' }[t] || t);

const statusLabel = (s) =>
    ({ active: 'Actif', expired: 'Expiré', suspended: 'Suspendu' }[s] || s);

const statusClass = (s) =>
    ({ active: 'bg-success', expired: 'bg-secondary', suspended: 'bg-warning text-dark' }[s] || 'bg-secondary');

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'short', year: 'numeric',
    });
};

const formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return '-';
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 }).format(amount);
};

const hasContracts = computed(() => contractsData.value?.data?.length > 0);

// ── Data fetching (SPA: JSON via Accept header) ──
const fetchContracts = async (page) => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        if (filterClientId.value) params.append('client_id', filterClientId.value);
        if (filterStatus.value) params.append('status', filterStatus.value);
        if (page) params.append('page', page);

        const res = await fetch('/it/maintenance-contracts?' + params.toString(), {
            headers: { Accept: 'application/json' },
        });
        if (res.ok) {
            contractsData.value = await res.json();
        }
    } catch (e) {
        console.error('Erreur de chargement des contrats:', e);
    } finally {
        loading.value = false;
    }
};

// ── Watchers ──
watch([filterClientId, filterStatus], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchContracts(), 300);
});

// ── Pagination ──
const goToPage = (url) => {
    if (!url) return;
    const u = new URL(url, window.location.origin);
    fetchContracts(u.searchParams.get('page'));
};

// ── Lifecycle ──
onMounted(() => {
    contractsData.value = props.contracts;
});
</script>

<template>
    <GelLayout page-title="Contrats de Maintenance">
        <div>
            <!-- ══ HEADER ══ -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-0" style="color:#163A5E;">Contrats de maintenance</h4>
                    <p class="text-muted small mb-0">Gestion des contrats de maintenance IT</p>
                </div>
                <a href="/it/maintenance-contracts/create" class="btn btn-primary btn-sm flex-shrink-0">
                    <i class="bi-plus-lg me-1"></i>Nouveau contrat
                </a>
            </div>

            <!-- ══ FILTRES ══ -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <select v-model="filterClientId" class="form-select form-select-sm" style="width:auto; min-width:180px;">
                    <option value="">Tous les clients</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                </select>
                <select v-model="filterStatus" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="active">Actif</option>
                    <option value="expired">Expiré</option>
                    <option value="suspended">Suspendu</option>
                </select>
                <span v-if="contractsData" class="small text-muted ms-1">
                    {{ contractsData.total }} contrat(s)
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
                                <th>Réf.</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th class="text-end">Montant mensuel</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!hasContracts">
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi-file-text" style="font-size:32px; color:#dce3ee; display:block; margin-bottom:8px;"></i>
                                    Aucun contrat trouvé.
                                </td>
                            </tr>
                            <tr v-for="contract in contractsData?.data" :key="contract.id">
                                <td>
                                    <a :href="'/it/maintenance-contracts/' + contract.id"
                                       class="text-decoration-none fw-medium"
                                       style="font-family:'SF Mono','Fira Code','Consolas',monospace; font-size:12px;">
                                        {{ contract.reference }}
                                    </a>
                                    <div class="small text-muted">{{ contract.title }}</div>
                                </td>
                                <td class="small">{{ contract.client?.company_name || '-' }}</td>
                                <td><span class="badge bg-info">{{ typeLabel(contract.type) }}</span></td>
                                <td>
                                    <span class="badge" :class="statusClass(contract.status)">
                                        {{ statusLabel(contract.status) }}
                                    </span>
                                </td>
                                <td class="small">{{ formatDate(contract.start_date) }}</td>
                                <td class="small">{{ formatDate(contract.end_date) }}</td>
                                <td class="text-end fw-medium">{{ formatCurrency(contract.monthly_amount) }}</td>
                                <td class="text-end">
                                    <a :href="'/it/maintenance-contracts/' + contract.id"
                                       class="btn btn-sm btn-outline-secondary" title="Voir le détail">
                                        <i class="bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ══ PAGINATION ══ -->
                <div v-if="contractsData?.last_page > 1"
                     class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">
                        Page {{ contractsData.current_page }} / {{ contractsData.last_page }}
                        ({{ contractsData.total }} résultat{{ contractsData.total > 1 ? 's' : '' }})
                    </small>
                    <nav aria-label="Pagination des contrats">
                        <ul class="pagination pagination-sm mb-0">
                            <li v-for="(link, i) in contractsData.links" :key="i"
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
