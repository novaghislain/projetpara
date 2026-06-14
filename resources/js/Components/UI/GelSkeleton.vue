<template>
  <div class="gel-skeleton" :style="{ width, height, borderRadius: rounded ? '50px' : '8px' }" />
</template>

<script setup>
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
