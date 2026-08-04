<template>
  <div ref="counterEl" class="gel-counter">
    <span class="counter-value">{{ count }}</span>
    <span v-if="suffix" class="counter-suffix">{{ suffix }}</span>
  </div>
</template>

<script setup>
/*
 * Composant : GelCounter
 * Role : Affiche un compteur animé qui se déclenche lors du défilement (intersection observer)
 * Props :
 *   target   (Number, requis)       — Valeur cible du compteur
 *   suffix   (String, défaut '')    — Texte suffixe (ex: +, %, €)
 *   duration (Number, défaut 2000)  — Durée de l'animation en ms
 */
import { ref, onMounted } from 'vue'
import { useCountUp } from '@/composables/useCountUp'
import { useIntersectionObserver } from '@vueuse/core'

const props = defineProps({
  target: { type: Number, required: true },
  suffix: { type: String, default: '' },
  duration: { type: Number, default: 2000 }
})

const counterEl = ref(null)
const { count, start } = useCountUp(props.target, props.duration)

// Déclenche l'animation lorsque l'élément devient visible (50% visible)
useIntersectionObserver(
  counterEl,
  ([{ isIntersecting }]) => {
    if (isIntersecting) start()
  },
  { threshold: 0.5 }
)
</script>

<style scoped>
.gel-counter { font-size: 2rem; font-weight: 600; }
.counter-suffix { margin-left: 0.25rem; }
</style>
