<template>
  <a :href="whatsappUrl" target="_blank" class="gel-whatsapp-btn" title="Contacter GEL Cabinet sur WhatsApp">
    <i class="bi bi-whatsapp fs-4"></i>
    <span class="gel-whatsapp-label">Besoin d'aide ?</span>
  </a>
</template>

<script setup>
/*
 * Composant : GelWhatsappButton
 * Role : Bouton flottant WhatsApp qui ouvre une conversation pré-remplie vers le support GEL Cabinet
 * Props : aucune
 */
const phone = '22900000000'
const message = encodeURIComponent(
  "Bonjour GEL Cabinet, j'aimerais avoir des informations sur vos services."
)
const whatsappUrl = `https://wa.me/${phone}?text=${message}`
</script>
