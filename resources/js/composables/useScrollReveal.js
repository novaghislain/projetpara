import { onMounted } from 'vue'

export function useScrollReveal() {
  onMounted(() => {
    const elements = document.querySelectorAll('.gel-reveal, .gel-reveal-left, .gel-reveal-right')
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('gel-revealed')
          observer.unobserve(entry.target)
        }
      })
    }, { threshold: 0.15 })
    elements.forEach(el => observer.observe(el))
  })
}
