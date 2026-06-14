<template>
  <div class="gel-slider" @mouseenter="pause" @mouseleave="resume">
    <div class="slider-track">
      <Transition name="gel-slide" mode="out-in">
        <div class="testimonial-card" :key="currentIndex">
          <p class="testimonial-text">"{{ testimonials[currentIndex].text }}"</p>
          <div class="testimonial-author">
            <strong>{{ testimonials[currentIndex].name }}</strong>
            <span>{{ testimonials[currentIndex].role }}</span>
          </div>
        </div>
      </Transition>
    </div>
    <button class="slider-prev" @click="prev">&#8592;</button>
    <button class="slider-next" @click="next">&#8594;</button>
    <div class="slider-dots">
      <button v-for="(_, i) in testimonials" :key="i" :class="['slider-dot', { active: i === currentIndex }]" @click="goTo(i)" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps({ testimonials: { type: Array, required: true } })
const currentIndex = ref(0)
let timer = null
const next = () => {
  currentIndex.value = (currentIndex.value + 1) % props.testimonials.length
}
const prev = () => {
  currentIndex.value = (currentIndex.value - 1 + props.testimonials.length) % props.testimonials.length
}
const goTo = i => { currentIndex.value = i }
const start = () => { timer = setInterval(next, 5000) }
const pause = () => { clearInterval(timer) }
const resume = () => { start() }
start()
</script>
