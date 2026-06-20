<template>
    <GelLayout page-title="Nouvelle Analyse OCR">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6" style="max-width:600px;">
                <h2 class="text-xl font-semibold mb-4">Analyser un document</h2>
                <form method="POST" action="/ocr" enctype="multipart/form-data">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">Sélectionner un client</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document (PDF, PNG, JPG — max 20 Mo)</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.tiff" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document lié (optionnel)</label>
                        <select name="document_id" class="form-select">
                            <option value="">— Aucun —</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi-scanner me-1"></i> Lancer l'analyse
                    </button>
                </form>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['clients'])
const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';
</script>
