<template>
    <GelLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-3"><h5 class="mb-0"><i class="bi-diagram-3 me-2"></i>{{ workflow ? 'Modifier' : 'Nouveau' }} workflow</h5></div>
            <form @submit.prevent="submit">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw500">Nom</label><input class="form-control form-control-sm" v-model="form.name" required></div>
                    <div class="col-md-6"><label class="form-label fw500">Client</label>
                        <select class="form-select form-select-sm" v-model="form.client_id" required><option value="">Sélectionner</option><option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option></select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw500">Modèle déclencheur</label><input class="form-control form-control-sm" v-model="form.trigger_model" placeholder="ex: Invoice" required></div>
                    <div class="col-md-2"><label class="form-label fw500">Actif</label><div class="form-check mt-2"><input type="checkbox" class="form-check-input" v-model="form.is_active" :true-value="1" :false-value="0"></div></div>
                    <div class="col-12"><label class="form-label fw500">Condition (JSON)</label><textarea class="form-control form-control-sm" rows="3" v-model="form.trigger_condition" placeholder='{"min_amount": 500000}'></textarea></div>
                    <div class="col-12">
                        <label class="form-label fw500">Étapes d'approbation</label>
                        <div v-for="(step, i) in form.steps" :key="i" class="row g-2 mb-2 align-items-center">
                            <div class="col-2"><input class="form-control form-control-sm" v-model="step.step_number" placeholder="Ordre" type="number"></div>
                            <div class="col-4"><input class="form-control form-control-sm" v-model="step.approver_role" placeholder="Rôle (ex: manager)"></div>
                            <div class="col-4"><input class="form-control form-control-sm" v-model="step.action" placeholder="Action (ex: approve)"></div>
                            <div class="col-2"><button type="button" class="btn btn-danger btn-sm" @click="removeStep(i)"><i class="bi-x"></i></button></div>
                        </div>
                        <button type="button" class="btn btn-light btn-sm" @click="addStep"><i class="bi-plus"></i> Ajouter une étape</button>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi-save me-1"></i> Enregistrer</button>
                    <a :href="route('gel.approval-workflows.index')" class="btn btn-light ms-2">Annuler</a>
                </div>
            </form>
        </div>
    </GelLayout>
</template>
<script setup>
import { reactive } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
const props = defineProps({ workflow: { type: Object, default: null }, clients: { type: Array, default: () => [] } })
const form = reactive({
    name: props.workflow?.name || '', client_id: props.workflow?.client_id || '', trigger_model: props.workflow?.trigger_model || '',
    trigger_condition: props.workflow?.trigger_condition ? JSON.stringify(props.workflow.trigger_condition) : '',
    is_active: props.workflow?.is_active ?? 1, steps: props.workflow?.steps || [{ step_number: 1, approver_role: '', action: 'approve' }]
})
const addStep = () => { form.steps.push({ step_number: form.steps.length + 1, approver_role: '', action: 'approve' }) }
const removeStep = (i) => { form.steps.splice(i, 1) }
const submit = () => {
    const data = { ...form, trigger_condition: form.trigger_condition ? JSON.parse(form.trigger_condition) : null, steps: form.steps }
    const isEdit = !!props.workflow; const url = isEdit ? `/approval-workflows/${props.workflow.id}` : '/approval-workflows'
    fetch(url, { method: isEdit ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }, body: JSON.stringify(data) })
        .then(r => { if (r.ok) window.location = '/approval-workflows'; else alert('Erreur') })
}
</script>
