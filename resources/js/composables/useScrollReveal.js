/* ============================================================
 * Fichier : composables/useScrollReveal.js
 * Description : Composable Vue pour l'animation au defilement
 * Utilise IntersectionObserver pour reveler les elements
 * avec les classes .gel-reveal, .gel-reveal-left, .gel-reveal-right
 * ============================================================ */

import { onMounted } from 'vue'

// --- Hook de scroll-reveal ---
// Observe les elements cibles et ajoute la classe gel-revealed lors de l'entree dans le viewport
export function useScrollReveal() {
  onMounted(() => {
    // Selectionne tous les elements a animer au defilement
    const elements = document.querySelectorAll('.gel-reveal, .gel-reveal-left, .gel-reveal-right')
    // Cree un observateur d'intersection avec un seuil de 15%
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Active l'animation lorsque l'element devient visible
          entry.target.classList.add('gel-revealed')
          // Desactive l'observation apres le declenchement (une seule fois)
          observer.unobserve(entry.target)
        }
      })
    }, { threshold: 0.15 })
    // Lance l'observation sur chaque element cible
    elements.forEach(el => observer.observe(el))
  })
}
