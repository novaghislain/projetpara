<template>
    <GelLayout page-title="Analyse OCR">
        <div class="p-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="text-xl fw-bold mb-0">Analyses OCR</h2>
                <a href="/ocr/create" class="btn btn-primary btn-sm">
                    <i class="bi-plus-circle me-1"></i> Nouvelle analyse
                </a>
            </div>

            <!-- Filtres -->
            <div class="bg-white rounded-lg shadow p-3 mb-3">
                <form class="row g-2 align-items-end" method="GET">
                    <div class="col-md-4">
                        <label class="form-label small">Client</label>
                        <select name="client_id" class="form-select form-select-sm" @change="this.form.submit()">
                            <option value="">Tous</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Statut</label>
                        <select name="status" class="form-select form-select-sm" @change="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="completed">Complété</option>
                            <option value="processing">En cours</option>
                            <option value="failed">Échoué</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Liste -->
            <div class="bg-white rounded-lg shadow">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fichier</th>
                                <th>Client</th>
                                <th>Statut</th>
                                <th>Confiance</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="scan in scans.data" :key="scan.id">
                                <td class="text-truncate" style="max-width:250px;">
                                    <i class="bi-file-earmark-text me-1"></i>
                                    {{ scan.original_filename }}
                                </td>
                                <td>{{ scan.client?.company_name || '—' }}</td>
                                <td>
                                    <span :class="'badge ' + statusClass(scan.status)">{{ scan.status }}</span>
                                </td>
                                <td>{{ scan.confidence != null ? scan.confidence + '%' : '—' }}</td>
                                <td>{{ scan.created_at }}</td>
                                <td>
                                    <a :href="`/ocr/${scan.id}`" class="btn btn-outline-primary btn-sm">
                                        <i class="bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!scans.data.length">
                                <td colspan="6" class="text-center text-muted py-4">Aucune analyse OCR.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-2" v-if="scans.last_page > 1">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item" :class="{ disabled: scans.current_page === 1 }">
                                <a class="page-link" :href="scans.path + '?page=' + (scans.current_page - 1)">‹</a>
                            </li>
                            <li class="page-item" v-for="p in scans.last_page" :key="p" :class="{ active: p === scans.current_page }">
                                <a class="page-link" :href="scans.path + '?page=' + p">{{ p }}</a>
                            </li>
                            <li class="page-item" :class="{ disabled: scans.current_page === scans.last_page }">
                                <a class="page-link" :href="scans.path + '?page=' + (scans.current_page + 1)">›</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['scans', 'clients'])

const statusClass = (s) => ({
    completed: 'bg-success',
    processing: 'bg-warning text-dark',
    pending: 'bg-secondary',
    failed: 'bg-danger',
}[s] || 'bg-secondary')
</script>
