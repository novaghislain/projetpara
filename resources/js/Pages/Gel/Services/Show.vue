<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    serviceId: { type: [Number, String], required: true }
});

const service = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchService = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/services/' + props.serviceId);
        if (!res.ok) throw new Error('Erreur de chargement');
        service.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchService);
</script>

<template>
    <GelLayout :page-title="service?.name || 'Service'">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="service">
            <!-- Header -->
            <div class="card card-dashboard mb-4">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center"
                             :style="{ backgroundColor: (service.color || '#1a237e') + '20', width: '56px', height: '56px', borderRadius: '14px' }">
                            <i :class="service.icon || 'bi-gear'" :style="{ color: service.color || '#1a237e', fontSize: '28px' }"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">{{ service.name }}</h4>
                            <span class="small text-muted">{{ service.slug }}</span>
                            <span class="badge ms-2" :class="service.is_active ? 'bg-success' : 'bg-secondary'">
                                {{ service.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <a :href="'/services/' + service.id + '/edit'" class="btn btn-sm btn-outline-primary">
                            <i class="bi-pencil me-1"></i>Modifier
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card card-dashboard p-3">
                        <h6 class="fw-bold mb-3"><i class="bi-info-circle me-2 text-primary"></i>Description</h6>
                        <p class="small mb-0">{{ service.description || 'Aucune description.' }}</p>
                        <hr>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted">Icône:</span>
                            <i :class="service.icon || 'bi-gear'"></i>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="small text-muted">Couleur:</span>
                            <span class="badge" :style="{ backgroundColor: service.color || '#6c757d' }">&nbsp;&nbsp;&nbsp;</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card card-dashboard">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0"><i class="bi-building me-2 text-success"></i>Clients utilisant ce service ({{ service.clients?.length || 0 }})</h6>
                        </div>
                        <div class="card-body p-0">
                            <div v-if="!service.clients?.length" class="text-muted small py-4 text-center">Aucun client n'utilise ce service pour le moment.</div>
                            <table v-else class="table table-hover align-middle mb-0">
                                <thead class="small text-muted">
                                    <tr><th>Société</th><th>Email</th><th>Statut</th><th>Assigné le</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="client in service.clients" :key="client.id">
                                        <td><a :href="'/clients/' + client.id" class="text-decoration-none fw-medium">{{ client.company_name }}</a></td>
                                        <td class="small">{{ client.email }}</td>
                                        <td>
                                            <span class="badge" :class="client.pivot?.status === 'actif' ? 'bg-success' : 'bg-secondary'">
                                                {{ client.pivot?.status || '-' }}
                                            </span>
                                        </td>
                                        <td class="small">{{ client.pivot?.created_at ? $formatDate(client.pivot.created_at) : '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </GelLayout>
</template>
