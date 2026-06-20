<template>
    <GelLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-3">
                <h5 class="mb-0"><i class="bi-pen me-2"></i>Signature #{{ signature.id }}</h5>
            </div>
            <div class="p-3">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Document :</strong> {{ signature.document?.title || 'N/A' }}</p>
                        <p><strong>Signataire :</strong> {{ signature.signer_name }}</p>
                        <p><strong>Email :</strong> {{ signature.signer_email }}</p>
                        <p><strong>Téléphone :</strong> {{ signature.signer_phone || '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Statut :</strong> <span class="badge" :class="signature.signed_at ? 'bg-success' : 'bg-warning text-dark'">{{ signature.signed_at ? 'Signé' : 'En attente' }}</span></p>
                        <p><strong>Signé le :</strong> {{ signature.signed_at ? new Date(signature.signed_at).toLocaleString('fr-FR') : '—' }}</p>
                        <p><strong>IP :</strong> {{ signature.ip_address || '—' }}</p>
                        <p><strong>Token :</strong> <code>{{ signature.token }}</code></p>
                    </div>
                </div>
                <a :href="route('gel.document-signatures.index')" class="btn btn-primary btn-sm"><i class="bi-arrow-left me-1"></i> Retour</a>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue'
defineProps({ signature: { type: Object, required: true } })
</script>
