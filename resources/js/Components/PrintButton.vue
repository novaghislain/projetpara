<script setup>
/*
 * Composant : PrintButton
 * Role : Bouton d'impression réutilisable, déclenche window.print()
 * Props :
 *   title    (String, défaut 'Imprimer')  — Texte du bouton
 *   filename (String, défaut 'document')  — Nom du document
 *   variant  (String, défaut 'outline-primary') — Variante Bootstrap du bouton
 *   size     (String, défaut 'sm')        — Taille du bouton
 */
defineProps({
    title: { type: String, default: 'Imprimer' },
    filename: { type: String, default: 'document' },
    variant: { type: String, default: 'outline-primary' },
    size: { type: String, default: 'sm' },
});

// Déclenche l'impression du document via le navigateur
function print() {
    window.print();
}
</script>

<template>
    <button class="btn d-inline-flex align-items-center gap-1" :class="'btn-' + variant + ' btn-' + size" @click="print">
        <i class="bi-printer"></i>
        <span>{{ title }}</span>
    </button>
</template>
