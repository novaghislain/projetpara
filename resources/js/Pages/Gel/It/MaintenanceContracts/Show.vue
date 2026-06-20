<template>
    <GelLayout page-title="Contrat de Maintenance">
        <div class="p-fluid">

            <!-- ══ HEADER ══ -->
            <div class="bg-white rounded-lg shadow p-6 mb-4">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div style="background:#fff3e0; color:#FF7900; width:48px; height:48px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="bi-file-earmark-check" style="font-size:22px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="h4 fw-bold mb-0">
                            {{ contract.reference }}
                            <span v-if="contract.title" class="ms-2" style="font-weight:400; font-size:14px; color:#888;">
                                &mdash; {{ contract.title }}
                            </span>
                        </div>
                        <div class="small text-muted d-flex flex-wrap gap-2 mt-1">
                            <span class="badge" :class="statusClass(contract.status)">
                                {{ statusLabel(contract.status) }}
                            </span>
                            <span class="badge bg-dark">
                                {{ typeLabel(contract.type) }}
                            </span>
                            <span v-if="contract.auto_renew" class="badge bg-success">
                                <i class="bi-arrow-repeat me-1"></i>Renouvellement auto
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a :href="'/it/maintenance-contracts/' + contract.id + '/edit'" class="btn btn-primary btn-sm">
                            <i class="bi-pencil me-1"></i>Modifier
                        </a>
                        <button class="btn btn-danger btn-sm" @click="confirmDelete">
                            <i class="bi-trash3 me-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══ 2-COLUMN DETAILS ══ -->
            <div class="row g-3 mt-2">
                <!-- Left column — Information -->
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-6 mb-4">
                        <div class="fw-bold mb-3">
                            <i class="bi-building me-2" style="color:#FF7900;"></i>Client
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Client</span>
                                <span class="fw-semibold" style="color:#163A5E;">
                                    {{ contract.client?.company_name || contract.client?.name || '-' }}
                                </span>
                            </div>
                            <div v-if="contract.client?.email" class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Email</span>
                                <span>{{ contract.client.email }}</span>
                            </div>
                            <div v-if="contract.client?.phone" class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">T&eacute;l&eacute;phone</span>
                                <span>{{ contract.client.phone }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                        <div class="fw-bold mb-3">
                            <i class="bi-calendar-event me-2" style="color:#FF7900;"></i>P&eacute;riode &amp; statut
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">R&eacute;f&eacute;rence</span>
                                <span style="font-family:monospace; font-size:12px;">
                                    {{ contract.reference || '-' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Type</span>
                                <span>
                                    <span class="badge bg-dark">{{ typeLabel(contract.type) }}</span>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Statut</span>
                                <span>
                                    <span class="badge" :class="statusClass(contract.status)">
                                        {{ statusLabel(contract.status) }}
                                    </span>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Date de d&eacute;but</span>
                                <span>{{ formatDate(contract.start_date) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Date de fin</span>
                                <span :class="isExpired ? 'text-danger fw-semibold' : ''">
                                    {{ formatDate(contract.end_date) }}
                                    <i v-if="isExpired" class="bi-exclamation-triangle-fill ms-1" style="color:#e53935;" title="Contrat expir&eacute;"></i>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Renouvellement auto.</span>
                                <span>
                                    <span v-if="contract.auto_renew" class="text-success"><i class="bi-check-circle-fill"></i> Oui</span>
                                    <span v-else class="text-muted">Non</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column — Financial & SLA -->
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-6 mb-4">
                        <div class="fw-bold mb-3">
                            <i class="bi-cash-coin me-2" style="color:#FF7900;"></i>Tarification
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Montant mensuel</span>
                                <span class="fw-bold" style="color:#163A5E; font-size:15px;">
                                    {{ formatCurrency(contract.monthly_amount) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Montant annuel estim&eacute;</span>
                                <span class="fw-bold" style="color:#163A5E; font-size:15px;">
                                    {{ formatCurrency(estimatedAnnual) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Heures incluses / mois</span>
                                <span>
                                    {{ contract.included_hours != null ? contract.included_hours + ' h' : '-' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">D&eacute;lai d'intervention</span>
                                <span>
                                    {{ contract.response_time_hours != null ? contract.response_time_hours + ' h' : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                        <div class="fw-bold mb-3">
                            <i class="bi-shield-check me-2" style="color:#FF7900;"></i>Couverture SLA
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Plage horaire</span>
                                <span>{{ contract.coverage_hours || 'Non d&eacute;fini' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">D&eacute;lai de r&eacute;ponse</span>
                                <span>
                                    {{ contract.response_time_hours != null ? contract.response_time_hours + ' heure(s)' : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes / Description -->
                    <div v-if="contract.description" class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                        <div class="fw-bold mb-3">
                            <i class="bi-journal-text me-2" style="color:#FF7900;"></i>Description
                        </div>
                        <div>
                            <p class="mb-0" style="white-space:pre-wrap;">{{ contract.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ ASSETS UNDER CONTRACT ══ -->
            <div v-if="contract.assets?.length" class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                <div class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi-laptop me-2" style="color:#FF7900;"></i>Actifs couverts</span>
                    <span class="small text-muted">{{ contract.assets.length }} actif(s)</span>
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>Tag</th>
                                    <th>Nom</th>
                                    <th>Cat&eacute;gorie</th>
                                    <th>Marque / Mod&egrave;le</th>
                                    <th>N&deg; de s&eacute;rie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="asset in contract.assets" :key="asset.id">
                                    <td>
                                        <a :href="'/it/assets/' + asset.id" class="text-decoration-none fw-medium" style="font-family:monospace; color:#FF7900;">
                                            {{ asset.asset_tag || '#' + asset.id }}
                                        </a>
                                    </td>
                                    <td class="fw-semibold" style="color:#163A5E;">{{ asset.name || '-' }}</td>
                                    <td style="font-size:12px;">{{ asset.category || '-' }}</td>
                                    <td style="font-size:12px;">{{ asset.brand || '' }} {{ asset.model || '' }}</td>
                                    <td style="font-size:11px; font-family:monospace;">{{ asset.serial_number || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══ TICKETS UNDER THIS CONTRACT ══ -->
            <div v-if="contract.tickets?.length" class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                <div class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi-ticket me-2" style="color:#FF7900;"></i>Tickets associ&eacute;s</span>
                    <span class="small text-muted">{{ contract.tickets.length }} ticket(s)</span>
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Titre</th>
                                    <th>Statut</th>
                                    <th>Priorit&eacute;</th>
                                    <th>Date cr&eacute;ation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ticket in contract.tickets" :key="ticket.id">
                                    <td>
                                        <a :href="'/it/tickets/' + ticket.id" class="text-decoration-none fw-medium" style="font-family:monospace; color:#FF7900;">
                                            #{{ ticket.id }}
                                        </a>
                                    </td>
                                    <td class="fw-semibold" style="color:#163A5E;">{{ ticket.title || '-' }}</td>
                                    <td>
                                        <span class="badge" :class="ticketStatusClass(ticket.status)">
                                            {{ ticket.status || '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="ticketPriorityClass(ticket.priority)">
                                            {{ ticket.priority || '-' }}
                                        </span>
                                    </td>
                                    <td style="font-size:12px;">{{ formatDate(ticket.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══ BACK LINK ══ -->
            <div class="mt-4">
                <a href="/it/maintenance-contracts" class="btn btn-outline-secondary btn-sm">
                    <i class="bi-arrow-left me-1"></i>Retour &agrave; la liste
                </a>
            </div>

            <!-- ══ DELETE CONFIRMATION MODAL ══ -->
            <div v-if="showDeleteModal" class="modal fade show d-block" style="background:rgba(0,0,0,0.5);" @click.self="showDeleteModal = false">
                <div class="modal-dialog modal-content">
                    <div class="modal-header">
                        <i class="bi-exclamation-triangle-fill me-2" style="color:#e53935;"></i>
                        Confirmer la suppression
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">
                            Êtes-vous s&ucirc;r de vouloir supprimer le contrat
                            <strong>{{ contract.reference }}</strong> ?
                        </p>
                        <p class="small text-muted mt-1 mb-0">Cette action est irr&eacute;versible.</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-end gap-2">
                        <button class="btn btn-sm btn-secondary" @click="showDeleteModal = false">
                            Annuler
                        </button>
                        <button class="btn btn-sm btn-danger" :disabled="deleting" @click="deleteContract">
                            <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi-trash3 me-1"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    contract: { type: Object, required: true },
});

// ── Modal ──
const showDeleteModal = ref(false);
const deleting = ref(false);

// ── Helpers (shared patterns from Index) ──

const typeLabel = (t) =>
    ({ corrective: 'Corrective', preventive: 'Préventive', full_service: 'Service complet', hotline: 'Hotline' }[t] || t || '-');

const statusLabel = (s) =>
    ({ active: 'Actif', expired: 'Expiré', suspended: 'Suspendu' }[s] || s || '-');

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

// ── Computed ──

const isExpired = computed(() => {
    if (!props.contract.end_date) return false;
    return new Date(props.contract.end_date) < new Date();
});

const estimatedAnnual = computed(() => {
    const m = props.contract.monthly_amount;
    if (m === null || m === undefined) return null;
    return Number(m) * 12;
});

const ticketStatusClass = (s) => {
    const map = {
        open: 'bg-info',
        in_progress: 'bg-warning text-dark',
        resolved: 'bg-success',
        closed: 'bg-secondary',
        cancelled: 'bg-dark',
    };
    return map[s] || 'bg-secondary';
};

const ticketPriorityClass = (p) => {
    const map = {
        critical: 'bg-danger',
        high: 'bg-warning text-dark',
        medium: 'bg-info',
        low: 'bg-secondary',
    };
    return map[p] || 'bg-secondary';
};

// ── Actions ──

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteContract = async () => {
    deleting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/it/maintenance-contracts/' + props.contract.id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });

        if (res.status === 302 || res.ok) {
            window.location.href = '/it/maintenance-contracts';
            return;
        }

        const errData = await res.json().catch(() => ({}));
        throw new Error(errData.message || "Erreur lors de la suppression");
    } catch (e) {
        alert(e.message);
    } finally {
        deleting.value = false;
        showDeleteModal.value = false;
    }
};
</script>
