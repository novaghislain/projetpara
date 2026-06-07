<script setup>
import { Facebook, Instagram, Twitter, Mail, Phone, MapPin } from 'lucide-vue-next';
</script>

<template>
    <footer class="bg-primary text-white pt-4 pb-3 mt-5 border-top border-white border-opacity-10">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-white rounded-3 p-1 overflow-hidden d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px">
                            <img src="/images/products/logopara.png" alt="Para Logo" class="img-fluid object-fit-contain" />
                        </div>
                        <h3 class="fw-bold mb-0 text-white ls-1">VICTOIRE PARA</h3>
                    </div>
                    <p class="small text-white text-opacity-75 mb-4 pe-lg-5" style="line-height: 1.8">
                        Votre destination privilégiée pour la santé naturelle et le bien-être au Bénin. Qualité certifiée et expertise pharmaceutique.
                    </p>
                    <div class="d-flex gap-3">
                        <a v-for="(Icon, i) in [Facebook, Instagram, Twitter]" :key="i" href="#" class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center text-white transition-all hover-bg-gold" style="width: 40px; height: 40px">
                            <component :is="Icon" :size="20" />
                        </a>
                    </div>
                </div>

                <div class="col-6 col-md-2 offset-md-1">
                    <h5 class="fw-bold mb-4 text-gold">LIENS UTILES</h5>
                    <ul class="list-unstyled small d-grid gap-2">
                        <li><a href="/" class="text-white text-opacity-75 text-decoration-none hover-text-gold">Accueil</a></li>
                        <li><a href="/shop" class="text-white text-opacity-75 text-decoration-none hover-text-gold">Boutique</a></li>
                        <li><a href="/services" class="text-white text-opacity-75 text-decoration-none hover-text-gold">Services</a></li>
                        <li><a href="/contact" class="text-white text-opacity-75 text-decoration-none hover-text-gold">Contact</a></li>
                    </ul>
                </div>

                <div class="col-12 col-md-4 offset-md-1">
                    <h5 class="fw-bold mb-4 text-gold">CONTACTS</h5>
                    <ul class="list-unstyled small d-grid gap-3">
                        <li class="d-flex align-items-start gap-3">
                            <MapPin :size="18" class="text-gold mt-1" />
                            <span class="text-white text-opacity-75">Cotonou, Bénin - Rue Pavée Fidjrossè</span>
                        </li>
                        <li class="d-flex align-items-center gap-3">
                            <Phone :size="18" class="text-gold" />
                            <span class="text-white text-opacity-75">+229 00 00 00 00</span>
                        </li>
                        <li class="d-flex align-items-center gap-3">
                            <Mail :size="18" class="text-gold" />
                            <span class="text-white text-opacity-75">contact@victoirepara.bj</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-4 border-top border-white border-opacity-10 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 text-center text-md-start">
                <p class="mb-0 small text-white text-opacity-50">© 2026 Victoire Para. Tous droits réservés.</p>
                <div class="d-flex gap-4 small">
                    <a href="#" class="text-white text-opacity-50 text-decoration-none hover-text-gold">Confidentialité</a>
                    <a href="#" class="text-white text-opacity-50 text-decoration-none hover-text-gold">Mentions légales</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.ls-1 { letter-spacing: 1px; }
.hover-bg-gold:hover { background-color: var(--accent-gold) !important; color: white !important; }
.hover-text-gold:hover { color: var(--accent-gold) !important; }
</style>
