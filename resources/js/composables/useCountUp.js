import { ref, computed } from 'vue'

export function useCountUp(target, duration = 2000) {
  const count = ref(0)

  const start = () => {
    const startTime = performance.now()
    const animate = (currentTime) => {
      const elapsed = currentTime - startTime
      const progress = Math.min(elapsed / duration, 1)
      const eased = 1 - Math.pow(1 - progress, 3)
      count.value = Math.floor(eased * target)
      if (progress < 1) {
        requestAnimationFrame(animate)
      } else {
        count.value = target
      }
    }
    requestAnimationFrame(animate)
  }

  return { count: computed(() => count.value), start }
}
