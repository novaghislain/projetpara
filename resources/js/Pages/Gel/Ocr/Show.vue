<template>
    <GelLayout page-title="Résultat OCR">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-xl font-semibold mb-0">{{ scan.original_filename }}</h2>
                    <span :class="'badge fs-6 ' + statusClass(scan.status)">{{ scan.status }}</span>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Client</label>
                        <p class="fw-bold">{{ scan.client?.company_name || '—' }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Type</label>
                        <p>{{ scan.mime_type }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Confiance</label>
                        <p>{{ scan.confidence != null ? scan.confidence + '%' : '—' }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Analysé le</label>
                        <p>{{ scan.processed_at || '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- Entités extraites -->
            <div class="bg-white rounded-lg shadow p-6 mb-3" v-if="scan.entities">
                <h5 class="fw-bold mb-3">Entités détectées</h5>
                <div class="row g-3">
                    <div class="col-md-3" v-if="scan.entities.amounts?.length">
                        <label class="form-label text-muted small">Montants</label>
                        <ul class="list-unstyled">
                            <li v-for="a in scan.entities.amounts" class="fw-bold text-success">{{ a.toFixed(2) }} €</li>
                        </ul>
                    </div>
                    <div class="col-md-3" v-if="scan.entities.dates?.length">
                        <label class="form-label text-muted small">Dates</label>
                        <ul class="list-unstyled">
                            <li v-for="d in scan.entities.dates">{{ d }}</li>
                        </ul>
                    </div>
                    <div class="col-md-3" v-if="scan.entities.emails?.length">
                        <label class="form-label text-muted small">Emails</label>
                        <ul class="list-unstyled">
                            <li v-for="e in scan.entities.emails">{{ e }}</li>
                        </ul>
                    </div>
                    <div class="col-md-3" v-if="scan.entities.ifu?.length">
                        <label class="form-label text-muted small">IFU</label>
                        <ul class="list-unstyled">
                            <li v-for="i in scan.entities.ifu" class="fw-bold">{{ i }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Texte extrait -->
            <div class="bg-white rounded-lg shadow p-6" v-if="scan.extracted_text">
                <h5 class="fw-bold mb-3">Texte extrait</h5>
                <pre class="bg-light p-3 rounded" style="white-space:pre-wrap;font-size:12px;max-height:400px;overflow-y:auto;">{{ scan.extracted_text }}</pre>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['scan'])

const statusClass = (s) => ({
    completed: 'bg-success',
    processing: 'bg-warning text-dark',
    pending: 'bg-secondary',
    failed: 'bg-danger',
}[s] || 'bg-secondary')
</script>
