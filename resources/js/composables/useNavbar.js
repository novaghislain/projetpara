/* ============================================================
 * Fichier : composables/useNavbar.js
 * Description : Composable Vue pour la barre de navigation
 * Detecte le defilement de la page pour appliquer des styles
 * Retourne un indicateur booleen isScrolled
 * ============================================================ */

import { ref, onMounted, onUnmounted } from 'vue'

// --- Hook de navigation ---
// Ecoute l'evenement scroll et met a jour l'etat isScrolled
export function useNavbar() {
  // Indique si la page a ete defilee au-dela de 80px
  const isScrolled = ref(false)

  // Met a jour isScrolled en fonction de la position verticale
  const handleScroll = () => {
    isScrolled.value = window.scrollY > 80
  }

  // Abonne l'ecouteur au montage du composant
  onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true })
  })

  // Nettoie l'ecouteur au demontage du composant
  onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
  })

  // Expose l'etat aux composants parents
  return { isScrolled }
}
