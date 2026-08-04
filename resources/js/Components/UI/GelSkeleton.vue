<template>
  <div class="gel-skeleton" :style="{ width, height, borderRadius: rounded ? '50px' : '8px' }" />
</template>

<script setup>
/*
 * Composant : GelSkeleton
 * Role : Placeholder de chargement (skeleton screen) pour les zones en attente de données
 * Props :
 *   width   (String, défaut '100%') — Largeur du squelette
 *   height  (String, défaut '20px') — Hauteur du squelette
 *   rounded (Boolean, défaut false) — Forme arrondie (cercle si true)
 */
import { computed } from 'vue'

const props = defineProps({
  width: { type: String, default: '100%' },
  height: { type: String, default: '20px' },
  rounded: { type: Boolean, default: false }
})

const width = computed(() => props.width)
const height = computed(() => props.height)
const rounded = computed(() => props.rounded)
</script>
