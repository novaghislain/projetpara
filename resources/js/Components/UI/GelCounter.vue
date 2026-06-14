<template>
  <div ref="counterEl" class="gel-counter">
    <span class="counter-value">{{ count }}</span>
    <span v-if="suffix" class="counter-suffix">{{ suffix }}</span>
  </div>
</template>

<script setup>
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
