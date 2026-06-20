<template>
    <span :class="badgeClass" class="legal-status-badge" role="status">
        <i v-if="icon" :class="icon" class="me-1" aria-hidden="true"></i>
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    statut: { type: String, default: '' },
    label:  { type: String, default: '' },
    icon:   { type: String, default: '' },
})

const statusColors = {
    // Contrats
    brouillon:       '--badge-gray',
    en_négociation:  '--badge-blue',
    'en_négociation':'--badge-blue',
    signé:           '--badge-green',
    actif:           '--badge-green',
    expiré:          '--badge-orange',
    résilié:         '--badge-red',
    suspendu:        '--badge-orange',
    contesté:        '--badge-red',
    // Contentieux
    en_cours:        '--badge-blue',
    instruction:     '--badge-blue',
    plaidoirie:      '--badge-orange',
    jugement_rendu:  '--badge-green',
    clôturé_gagné:   '--badge-green',
    clôturé_perdu:   '--badge-red',
    clôturé_transaction: '--badge-gray',
    // Conformité
    conforme:        '--badge-green',
    non_conforme:    '--badge-red',
    à_vérifier:      '--badge-orange',
    // Assemblées
    planifiée:       '--badge-blue',
    tenue:           '--badge-green',
    annulée:         '--badge-red',
    reportée:        '--badge-orange',
    // Dossiers
    ouvert:          '--badge-blue',
    en_attente:      '--badge-orange',
    clôturé:         '--badge-gray',
    archivé:         '--badge-gray',
    // Priorité
    normale:         '--badge-green',
    urgente:         '--badge-orange',
    critique:        '--badge-red',
}

const badgeClass = computed(() => {
    const s = props.statut?.trim().toLowerCase()
    if (s && statusColors[s]) return statusColors[s]
    if (s?.startsWith('clôturé') || s?.startsWith('cloturé')) return '--badge-gray'
    return '--badge-gray'
})
</script>

<style scoped>
.legal-status-badge {
    display: inline-block;
    padding: 0.2em 0.6em;
    font-size: 0.7rem;
    font-weight: 600;
    line-height: 1.4;
    border-radius: 50px;
    white-space: nowrap;
    vertical-align: middle;
}

/* ─── Palette sémantique ─────────────────────────────────────── */
.--badge-green  { background: #d1fae5; color: #065f46; }
.--badge-blue   { background: #dbeafe; color: #1e40af; }
.--badge-orange { background: #fef3c7; color: #92400e; }
.--badge-red    { background: #fee2e2; color: #b91c1c; }
.--badge-gray   { background: #f3f4f6; color: #6b7280; }
</style>
