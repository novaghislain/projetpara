<template>
    <div class="border rounded p-4 bg-white">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="fw-semibold mb-1">{{ title }}</h6>
                <span v-if="societe" class="text-muted small">{{ societe }}</span>
            </div>
            <span v-if="categorie" class="badge bg-light text-dark">{{ categorie }}</span>
        </div>

        <div v-if="html" class="preview-content border-top pt-3" v-html="html"></div>
        <div v-else class="text-muted text-center py-4">
            <i class="bi bi-file-text fs-1 d-block mb-2"></i>
            <small>Aperçu non disponible</small>
        </div>

        <div v-if="variables?.length" class="border-top pt-3 mt-3">
            <small class="text-muted fw-semibold">Variables requises :</small>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <span v-for="v in variables" :key="v.nom"
                      class="badge bg-light text-dark border">
                    <code>{{ '{{' + v.nom + '}}' }}</code>
                    <span v-if="v.description" class="ms-1 text-muted">— {{ v.description }}</span>
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, default: '' },
    societe: { type: String, default: '' },
    categorie: { type: String, default: '' },
    html: { type: String, default: '' },
    variables: { type: Array, default: () => [] },
});
</script>

<style scoped>
.preview-content {
    font-size: 0.875rem;
    line-height: 1.7;
}
.preview-content :deep(p) {
    margin-bottom: 0.5rem;
}
</style>
