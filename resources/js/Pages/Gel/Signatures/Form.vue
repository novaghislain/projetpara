<template>
    <GelLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-3">
                <h5 class="mb-0"><i class="bi-pen me-2"></i>{{ signature ? 'Modifier' : 'Nouvelle' }} demande de signature</h5>
            </div>
            <div>
                <form @submit.prevent="submit">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw500">Document</label>
                            <select class="form-select form-select-sm" v-model="form.document_id" required>
                                <option value="">Sélectionner un document</option>
                                <option v-for="d in documents" :key="d.id" :value="d.id">{{ d.title }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw500">Nom du signataire</label>
                            <input class="form-control form-control-sm" v-model="form.signer_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw500">Email</label>
                            <input type="email" class="form-control form-control-sm" v-model="form.signer_email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw500">Téléphone</label>
                            <input class="form-control form-control-sm" v-model="form.signer_phone">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw500">Message (optionnel)</label>
                            <textarea class="form-control form-control-sm" rows="3" v-model="form.message"></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi-send me-1"></i> Envoyer la demande</button>
                        <a :href="route('gel.document-signatures.index')" class="btn btn-light ms-2">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
import { reactive } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
const props = defineProps({ signature: { type: Object, default: null }, documents: { type: Array, default: () => [] } })
const form = reactive({ document_id: props.signature?.document_id || '', signer_name: props.signature?.signer_name || '', signer_email: props.signature?.signer_email || '', signer_phone: props.signature?.signer_phone || '', message: '' })
const submit = () => { const isEdit = !!props.signature; const url = isEdit ? `/signatures/${props.signature.id}` : '/signatures'; const method = isEdit ? 'PUT' : 'POST'; fetch(url, { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }, body: JSON.stringify(form) }).then(r => { if (r.ok) window.location = '/signatures'; else alert('Erreur') }) }
</script>
