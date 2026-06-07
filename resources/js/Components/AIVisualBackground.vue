<script setup>
import { ref, onMounted } from 'vue';
import { Apple, Citrus, Cherry, Leaf } from 'lucide-vue-next';

const nodes = [...Array(10).keys()];
const objects = [...Array(15).keys()];

// Randomize positions for objects on mount to avoid same patterns
const objectStates = ref(objects.map(() => ({
    x: Math.random() * 100 + '%',
    y: Math.random() * 100 + '%',
    delay: Math.random() * 5 + 's',
    duration: 15 + Math.random() * 20 + 's'
})));
</script>

<template>
  <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden pointer-events-none z-0">
    <!-- Neural Connections SVG -->
    <svg class="w-100 h-100 position-absolute top-0 start-0">
      <g v-for="i in nodes" :key="'group-'+i">
        <!-- Animated Glow Nodes -->
        <circle
          :cx="`${5 + i * 10}%`"
          :cy="`${10 + (i % 4) * 25}%`"
          :r="i % 2 === 0 ? '4' : '2'"
          :fill="i % 2 === 0 ? 'var(--bs-primary)' : 'var(--accent-gold)'"
          class="glow-node"
          :style="{ animationDelay: i * 0.5 + 's', animationDuration: (4 + i) + 's' }"
        />
        <!-- Connections -->
        <line
          v-if="i < 9"
          :x1="`${5 + i * 10}%`"
          :y1="`${10 + (i % 4) * 25}%`"
          :x2="`${5 + (i + 1) * 10}%`"
          :y2="`${10 + ((i + 1) % 4) * 25}%`"
          :stroke="i % 2 === 0 ? 'var(--bs-primary)' : 'var(--accent-gold)'"
          stroke-width="1"
          class="neural-line"
          :style="{ animationDelay: i * 0.8 + 's', animationDuration: (8 + i) + 's' }"
        />
      </g>
    </svg>
    
    <!-- Floating Foreground Objects -->
    <div v-for="(obj, i) in objectStates" :key="'obj-'+i" 
         class="position-absolute floating-obj"
         :class="i % 2 === 0 ? 'text-primary' : 'text-gold'"
         :style="{ 
           left: obj.x, 
           top: obj.y,
           animationDelay: obj.delay,
           animationDuration: obj.duration,
           zIndex: 10
         }">
        <div class="p-2 bg-white bg-opacity-20 rounded-circle backdrop-blur-md border border-white border-opacity-30 shadow-premium">
          <Apple v-if="i % 4 === 0" :size="24 + (i % 5)" />
          <Citrus v-else-if="i % 4 === 1" :size="22 + (i % 5)" />
          <Cherry v-else-if="i % 4 === 2" :size="20 + (i % 5)" />
          <Leaf v-else :size="18 + (i % 5)" />
        </div>
    </div>
  </div>
</template>

<style scoped>
.z-0 { z-index: 0; }
.backdrop-blur-md { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }

@keyframes glowScale {
  0%, 100% { transform: scale(1); opacity: 0.3; filter: blur(0px); }
  50% { transform: scale(1.8); opacity: 0.8; filter: blur(2px); }
}

.glow-node { 
  transform-origin: center;
  animation: glowScale 4s infinite ease-in-out;
}

@keyframes pathAnimation {
  0%, 100% { stroke-dashoffset: 100; opacity: 0.1; }
  50% { stroke-dashoffset: 0; opacity: 0.3; }
}

.neural-line {
    opacity: 0.2;
    stroke-dasharray: 100;
    animation: pathAnimation 8s infinite ease-in-out;
}

@keyframes floatAndRotate {
  0% { transform: translateY(0) translateX(0) rotate(0deg) scale(0.8); opacity: 0.4; }
  50% { transform: translateY(-50px) translateX(20px) rotate(180deg) scale(1.3); opacity: 0.9; }
  100% { transform: translateY(0) translateX(0) rotate(360deg) scale(0.8); opacity: 0.4; }
}

.floating-obj {
    animation: floatAndRotate 20s infinite ease-in-out;
}
</style>
