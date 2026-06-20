<template>
    <GelLayout page-title="Actif IT">
        <div class="p-fluid">

            <!-- ══ HEADER ══ -->
            <div class="bg-white rounded-lg shadow p-6 mb-4">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div style="background:#e3f2fd; color:#1565c0; width:48px; height:48px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="bi-laptop" style="font-size:22px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="h4 fw-bold mb-0">{{ asset.name }}</div>
                        <div class="small text-muted d-flex flex-wrap gap-2 mt-1">
                            <span class="badge" :style="statusStyle">
                                {{ statusLabel }}
                            </span>
                            <span class="badge" style="background:#163A5E; color:#fff;">
                                {{ categoryLabel }}
                            </span>
                            <span class="badge" style="background:#FF7900; color:#fff;">
                                {{ asset.asset_tag }}
                            </span>
                        </div>
                    </div>
                    <a :href="'/it/assets/' + asset.id + '/edit'" class="btn btn-primary btn-sm flex-shrink-0">
                        <i class="bi-pencil me-1"></i>Modifier
                    </a>
                </div>
            </div>

            <!-- ══ 2-COLUMN DETAILS ══ -->
            <div class="row g-3 mt-2">
                <!-- Left column -->
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-6 mb-4">
                        <div class="fw-bold mb-3">
                            <i class="bi-info-circle me-2" style="color:#FF7900;"></i>Identification
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Tag</span>
                                <span>{{ asset.asset_tag || '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Nom</span>
                                <span class="fw-semibold" style="color:#163A5E;">{{ asset.name || '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Catégorie</span>
                                <span>{{ categoryLabel }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Client</span>
                                <span>{{ asset.client?.company_name || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                        <div class="fw-bold mb-3">
                            <i class="bi-tag me-2" style="color:#FF7900;"></i>Marque & modele
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Marque</span>
                                <span>{{ asset.brand || '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Modèle</span>
                                <span>{{ asset.model || '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">N° de série</span>
                                <span style="font-family:monospace; font-size:12px;">{{ asset.serial_number || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                        <div class="fw-bold mb-3">
                            <i class="bi-diagram-2 me-2" style="color:#FF7900;"></i>Affectation & statut
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Statut</span>
                                <span>
                                    <span class="badge" :style="statusStyle">{{ statusLabel }}</span>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Assigné à</span>
                                <span>{{ asset.assigned_to ? asset.assigned_to.name : (asset.assignedTo?.name || '-') }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Localisation</span>
                                <span>{{ asset.location || '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-6 mb-4">
                        <div class="fw-bold mb-3">
                            <i class="bi-cash-coin me-2" style="color:#FF7900;"></i>Achat & garantie
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Date d'achat</span>
                                <span>{{ asset.purchase_date ? $formatDate(asset.purchase_date) : '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Prix d'achat (FCFA)</span>
                                <span>{{ asset.purchase_price ? Number(asset.purchase_price).toLocaleString() + ' FCFA' : '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Garantie jusqu'au</span>
                                <span :class="warrantyClass">
                                    {{ asset.warranty_expires_at ? $formatDate(asset.warranty_expires_at) : '-' }}
                                    <i v-if="isWarrantyExpired" class="bi-exclamation-triangle-fill ms-1" style="color:#e53935;" title="Garantie expiree"></i>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Prochaine maintenance</span>
                                <span>{{ asset.next_maintenance_at ? $formatDate(asset.next_maintenance_at) : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                        <div class="fw-bold mb-3">
                            <i class="bi-cpu me-2" style="color:#FF7900;"></i>Informations système
                        </div>
                        <div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">OS / Version</span>
                                <span>{{ asset.os_version || '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Adresse IP</span>
                                <span style="font-family:monospace;">{{ asset.ip_address || '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-medium text-muted small">Adresse MAC</span>
                                <span style="font-family:monospace;">{{ asset.mac_address || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                        <div class="fw-bold mb-3">
                            <i class="bi-journal-text me-2" style="color:#FF7900;"></i>Notes
                        </div>
                        <div>
                            <p class="mb-0" style="white-space:pre-wrap;">{{ asset.notes || 'Aucune note.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ LICENSES TABLE ══ -->
            <div class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                <div class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi-key me-2" style="color:#FF7900;"></i>Licences</span>
                    <span class="small text-muted">{{ asset.licenses?.length || 0 }} licence(s)</span>
                </div>
                <div class="p-0">
                    <div v-if="!asset.licenses?.length" class="text-muted small py-4 text-center">
                        <i class="bi-inbox me-2"></i>Aucune licence associee.
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Logiciel</th>
                                    <th>Editeur</th>
                                    <th>Cle de licence</th>
                                    <th>Places</th>
                                    <th>Prix</th>
                                    <th>Expire le</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="lic in asset.licenses" :key="lic.id">
                                    <td class="fw-semibold" style="color:#163A5E;">{{ lic.software_name }}</td>
                                    <td style="font-size:12px;">{{ lic.vendor || '-' }}</td>
                                    <td style="font-size:11px; font-family:monospace;">{{ lic.license_key || '-' }}</td>
                                    <td style="font-size:12px;">{{ lic.seats ?? '-' }}</td>
                                    <td style="font-size:12px;">{{ lic.purchase_price ? Number(lic.purchase_price).toLocaleString() + ' FCFA' : '-' }}</td>
                                    <td style="font-size:12px;">
                                        <span :class="lic.expires_at ? (new Date(lic.expires_at) < new Date() ? 'text-danger fw-semibold' : '') : ''">
                                            {{ lic.expires_at ? $formatDate(lic.expires_at) : '-' }}
                                        </span>
                                    </td>
                                    <td style="font-size:12px; max-width:180px;" class="text-truncate">{{ lic.notes || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══ INTERVENTIONS TABLE ══ -->
            <div class="bg-white rounded-lg shadow p-6 mb-4 mt-3">
                <div class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi-tools me-2" style="color:#FF7900;"></i>Interventions</span>
                    <span class="small text-muted">{{ asset.interventions?.length || 0 }} intervention(s)</span>
                </div>
                <div class="p-0">
                    <div v-if="!asset.interventions?.length" class="text-muted small py-4 text-center">
                        <i class="bi-inbox me-2"></i>Aucune intervention enregistree.
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Technicien</th>
                                    <th>Description</th>
                                    <th>Duree</th>
                                    <th>Cout</th>
                                    <th>Ticket</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="iv in asset.interventions" :key="iv.id">
                                    <td style="font-size:12px;">{{ iv.date ? $formatDate(iv.date) : '-' }}</td>
                                    <td>
                                        <span class="badge" :style="interventionTypeStyle(iv.type)">
                                            {{ iv.type || '-' }}
                                        </span>
                                    </td>
                                    <td style="font-size:12px;">{{ iv.technician?.name || iv.technician_id || '-' }}</td>
                                    <td style="font-size:12px; max-width:220px;" class="text-truncate">{{ iv.description || '-' }}</td>
                                    <td style="font-size:12px;">{{ iv.duration_minutes ? iv.duration_minutes + ' min' : '-' }}</td>
                                    <td style="font-size:12px;">{{ iv.cost ? Number(iv.cost).toLocaleString() + ' FCFA' : '-' }}</td>
                                    <td style="font-size:12px;">
                                        <a v-if="iv.ticket_id" :href="'/it/tickets/' + iv.ticket_id" class="text-decoration-none fw-medium" style="color:#FF7900;">
                                            #{{ iv.ticket_id }}
                                        </a>
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </GelLayout>
</template>

<script setup>
import { computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    asset: { type: Object, required: true },
});

// ── Computed helpers ──────────────────────────────────────────

const categoryLabels = {
    computer: 'Ordinateur',
    server: 'Serveur',
    printer: 'Imprimante',
    network: 'Reseau',
    mobile: 'Mobile',
    software: 'Logiciel',
    other: 'Autre',
};

const statusLabels = {
    active: 'Actif',
    inactive: 'Inactif',
    in_repair: 'En reparation',
    disposed: 'Reforme',
};

const categoryLabel = computed(() => categoryLabels[props.asset.category] || props.asset.category || '-');
const statusLabel = computed(() => statusLabels[props.asset.status] || props.asset.status || '-');

const statusStyle = computed(() => ({
    active: { background: '#4caf50', color: '#fff' },
    inactive: { background: '#9e9e9e', color: '#fff' },
    in_repair: { background: '#ff9800', color: '#fff' },
    disposed: { background: '#424242', color: '#fff' },
}[props.asset.status] || { background: '#9e9e9e', color: '#fff' }));

const isWarrantyExpired = computed(() => {
    if (!props.asset.warranty_expires_at) return false;
    return new Date(props.asset.warranty_expires_at) < new Date();
});

const warrantyClass = computed(() => {
    if (!props.asset.warranty_expires_at) return '';
    return isWarrantyExpired.value ? 'text-danger' : '';
});

const interventionTypeStyle = (type) => ({
    background: {
        maintenance: '#1565c0',
        repair: '#ff9800',
        installation: '#4caf50',
        diagnostic: '#7b1fa2',
    }[type] || '#9e9e9e',
    color: '#fff',
});
</script>
