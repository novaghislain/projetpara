<template>
    <GelLayout page-title="Centre de Coût">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-xl font-semibold mb-0">{{ center.code }} — {{ center.name }}</h2>
                    <a :href="`/cost-centers/${center.id}/edit`" class="btn btn-primary btn-sm">
                        <i class="bi-pencil me-1"></i> Modifier
                    </a>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Code</label>
                        <p class="fw-bold">{{ center.code }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Type</label>
                        <p><span class="badge bg-info">{{ center.type }}</span></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Client</label>
                        <p>{{ center.client?.company_name || 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Centre parent</label>
                        <p>{{ center.parent?.name || 'Aucun' }}</p>
                    </div>
                    <div class="col-md-6" v-if="center.children?.length">
                        <label class="form-label text-muted small">Centres enfants ({{ center.children.length }})</label>
                        <ul class="list-unstyled">
                            <li v-for="child in center.children" :key="child.id">
                                <a :href="`/cost-centers/${child.id}`" class="text-decoration-none">
                                    <i class="bi-diagram-2 me-1"></i> {{ child.code }} — {{ child.name }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Statut</label>
                        <p><span :class="'badge ' + (center.is_active ? 'bg-success' : 'bg-secondary')">
                            {{ center.is_active ? 'Actif' : 'Inactif' }}
                        </span></p>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['center'])
</script>
