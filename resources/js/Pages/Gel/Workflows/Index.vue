<template>
    <GelLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex-wrap d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi-diagram-3 me-2"></i>Workflows d'approbation</h5>
                <a :href="route('gel.approval-workflows.create')" class="btn btn-primary btn-sm"><i class="bi-plus-lg"></i> Nouveau workflow</a>
            </div>
            <div class="p-3">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" v-model="clientId" @change="filter"><option value="">Tous les clients</option><option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option></select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0"><thead><tr><th>Nom</th><th>Client</th><th>Déclencheur</th><th>Statut</th><th>Actions</th></tr></thead>
                        <tbody>
                            <tr v-for="w in workflows.data" :key="w.id">
                                <td>{{ w.name }}</td><td>{{ w.client?.company_name || 'N/A' }}</td><td><code>{{ w.trigger_model }}</code></td>
                                <td><span class="badge" :class="w.is_active?'bg-success':'bg-secondary'">{{ w.is_active?'Actif':'Inactif' }}</span></td>
                                <td>
                                    <a :href="`/approval-workflows/${w.id}`" class="btn btn-primary btn-sm me-1"><i class="bi-eye"></i></a>
                                    <a :href="`/approval-workflows/${w.id}/edit`" class="btn btn-warning btn-sm me-1"><i class="bi-pencil"></i></a>
                                </td>
                            </tr>
                            <tr v-if="!workflows.data?.length"><td colspan="5" class="text-center text-muted py-4">Aucun workflow.</td></tr>
                        </tbody>
                    </table>
                </div>
                <nav v-if="workflows.last_page>1" class="mt-3"><ul class="pagination pagination-sm mb-0"><li v-for="p in workflows.last_page" :key="p" class="page-item" :class="{active:p===workflows.current_page}"><a class="page-link" :href="`/approval-workflows?page=${p}`">{{ p }}</a></li></ul></nav>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
import { ref } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
defineProps({ workflows: { type: Object, default: () => ({ data:[], current_page:1, last_page:1 }) }, clients: { type: Array, default: () => [] } })
const clientId = ref('')
const filter = () => { window.location = `/approval-workflows?client_id=${clientId.value}` }
</script>
