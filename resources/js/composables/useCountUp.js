/* ============================================================
 * Fichier : composables/useCountUp.js
 * Description : Composable Vue pour l'animation de comptage
 * Anime un compteur de 0 jusqu'a une valeur cible
 * Utilise une fonction d'easing cubique pour un rendu fluide
 * ============================================================ */

import { ref, computed } from 'vue'

// --- Hook de compteur anime ---
// @target : valeur finale a atteindre
// @duration : duree de l'animation en millisecondes (defaut 2000ms)
export function useCountUp(target, duration = 2000) {
  // Valeur courante du compteur
  const count = ref(0)

  // Demarre l'animation du compteur
  const start = () => {
    const startTime = performance.now()
    // Boucle d'animation recursive via requestAnimationFrame
    const animate = (currentTime) => {
      const elapsed = currentTime - startTime
      // Progression normalisee entre 0 et 1
      const progress = Math.min(elapsed / duration, 1)
      // Fonction d'easing cubique (ease-out) pour un ralentissement progressif
      const eased = 1 - Math.pow(1 - progress, 3)
      // Calcule la valeur intermediaire
      count.value = Math.floor(eased * target)
      if (progress < 1) {
        // Continue l'animation si la duree n'est pas ecoulee
        requestAnimationFrame(animate)
      } else {
        // Fixe la valeur finale une fois l'animation terminee
        count.value = target
      }
    }
    requestAnimationFrame(animate)
  }

  // Expose la valeur reactive (lecture seule) et la fonction de demarrage
  return { count: computed(() => count.value), start }
}
