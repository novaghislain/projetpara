<template>
    <GelLayout>
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <div class="mb-3"><h5 class="mb-0"><i class="bi-diagram-3 me-2"></i>Workflow : {{ workflow.name }}</h5></div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Client :</strong> {{ workflow.client?.company_name || 'N/A' }}</p>
                    <p><strong>Modèle déclencheur :</strong> <code>{{ workflow.trigger_model }}</code></p>
                    <p><strong>Statut :</strong> <span class="badge" :class="workflow.is_active?'bg-success':'bg-secondary'">{{ workflow.is_active?'Actif':'Inactif' }}</span></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Condition :</strong> <code>{{ workflow.trigger_condition ? JSON.stringify(workflow.trigger_condition) : '—' }}</code></p>
                </div>
            </div>
            <h6 class="mt-3 mb-2 fw600">Étapes d'approbation</h6>
            <div v-if="workflow.steps?.length" class="d-flex flex-wrap gap-3">
                <div v-for="(step, i) in workflow.steps" :key="i" class="border rounded p-3" style="min-width:180px;">
                    <div class="fw700 fs-5 text-orange">{{ step.step_number }}</div>
                    <div><strong>Rôle :</strong> {{ step.approver_role }}</div>
                    <div><strong>Action :</strong> {{ step.action }}</div>
                </div>
            </div>
            <div v-else class="text-muted">Aucune étape définie.</div>
            <div class="mt-4">
                <a :href="route('gel.approval-workflows.index')" class="btn btn-primary btn-sm"><i class="bi-arrow-left me-1"></i> Retour</a>
                <a :href="`/approval-workflows/${workflow.id}/edit`" class="btn btn-warning btn-sm ms-2"><i class="bi-pencil me-1"></i> Modifier</a>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue'
defineProps({ workflow: { type: Object, required: true } })
</script>
