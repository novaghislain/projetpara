<template>
    <GelLayout page-title="Politique SLA">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-xl font-semibold mb-0">{{ policy.name }}</h2>
                    <div class="d-flex gap-2">
                        <a :href="`/it/sla-policies/${policy.id}/edit`" class="btn btn-primary btn-sm">
                            <i class="bi-pencil me-1"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Priorité</label>
                        <p><span :class="'badge ' + priorityClass(policy.priority)">{{ policy.priority }}</span></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Première réponse (heures)</label>
                        <p class="fw-bold">{{ policy.first_response_hours }}h</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Résolution (heures)</label>
                        <p class="fw-bold">{{ policy.resolution_hours }}h</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Statut</label>
                        <p><span :class="'badge ' + (policy.is_default ? 'bg-warning text-dark' : 'bg-secondary')">
                            {{ policy.is_default ? 'Par défaut' : 'Personnalisée' }}
                        </span></p>
                    </div>
                    <div class="col-12" v-if="policy.description">
                        <label class="form-label text-muted small">Description</label>
                        <p>{{ policy.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../../Layouts/GelLayout.vue';
defineProps(['policy'])

const priorityClass = (p) => ({
    critical: 'bg-danger',
    high: 'bg-warning text-dark',
    medium: 'bg-info',
    low: 'bg-secondary',
}[p] || 'bg-secondary')
</script>
