<template>
    <GelLayout page-title="Règle de Relance">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-xl font-semibold mb-0">{{ rule.name }}</h2>
                    <div class="d-flex gap-2">
                        <a :href="`/relance-rules/${rule.id}/edit`" class="btn btn-primary btn-sm">
                            <i class="bi-pencil me-1"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Client</label>
                        <p class="fw-bold">{{ rule.client?.company_name || 'Tous les clients' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Déclenchement (jours)</label>
                        <p>J-{{ rule.trigger_days }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Canal</label>
                        <p><span class="badge bg-primary">{{ rule.channel }}</span></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Statut</label>
                        <p><span :class="'badge ' + (rule.is_active ? 'bg-success' : 'bg-secondary')">
                            {{ rule.is_active ? 'Active' : 'Inactive' }}
                        </span></p>
                    </div>
                    <div class="col-12" v-if="rule.template_subject">
                        <label class="form-label text-muted small">Sujet du message</label>
                        <p class="p-2 bg-light rounded">{{ rule.template_subject }}</p>
                    </div>
                    <div class="col-12" v-if="rule.template_body">
                        <label class="form-label text-muted small">Corps du message</label>
                        <pre class="p-2 bg-light rounded" style="white-space:pre-wrap;">{{ rule.template_body }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['rule'])
</script>
