<template>
  <header class="gel-navbar">
    <div class="container-fluid">
      <!-- Logo -->
      <a href="/" class="gel-brand">
        <div class="gel-brand-logo">GEL</div>
        <div class="gel-brand-text">
          <span class="gel-brand-name">GEL Cabinet</span>
          <span class="gel-brand-sub">Gestion Multi-Pôles</span>
        </div>
      </a>

      <!-- Navigation centrale -->
      <ul class="gel-nav-center" id="gelNavCenter">
        <li class="gel-nav-item">
          <a href="/nos-modules" class="gel-nav-link">Nos Modules <i class="bi-chevron-down chevron"></i></a>
          <ul class="gel-dropdown">
            <li><a href="/nos-modules#pole-administration"><span class="drop-icon"><i class="bi-shield-lock"></i></span> Pôle Administration</a></li>
            <li><a href="/nos-modules#pole-comptabilite"><span class="drop-icon"><i class="bi-calculator"></i></span> Pôle Comptabilité / Finance</a></li>
            <li><a href="/nos-modules#pole-fiscal"><span class="drop-icon"><i class="bi-receipt"></i></span> Pôle Fiscal</a></li>
            <li><a href="/nos-modules#pole-social"><span class="drop-icon"><i class="bi-people"></i></span> Pôle Social & Paie</a></li>
            <li><a href="/nos-modules#pole-juridique"><span class="drop-icon"><i class="bi-bank2"></i></span> Pôle Juridique</a></li>
            <li><a href="/nos-modules#pole-it"><span class="drop-icon"><i class="bi-laptop"></i></span> Pôle IT</a></li>
            <li><hr class="gel-dropdown-divider"></li>
            <li><a href="/nos-modules"><span class="drop-icon"><i class="bi-grid-3x3-gap"></i></span> Découvrir tous les modules</a></li>
          </ul>
        </li>
        <li class="gel-nav-item">
          <a href="/nos-services" class="gel-nav-link">Services <i class="bi-chevron-down chevron"></i></a>
          <ul class="gel-dropdown">
            <li><a href="/services/comptabilite"><span class="drop-icon"><i class="bi-calculator"></i></span> Comptabilité</a></li>
            <li><a href="/services/fiscal"><span class="drop-icon"><i class="bi-receipt"></i></span> Fiscal</a></li>
            <li><a href="/services/juridique"><span class="drop-icon"><i class="bi-bank2"></i></span> Juridique</a></li>
            <li><a href="/services/social-paie"><span class="drop-icon"><i class="bi-people"></i></span> Social & Paie</a></li>
            <li><hr class="gel-dropdown-divider"></li>
            <li><a href="/nos-services"><span class="drop-icon"><i class="bi-grid-3x3-gap"></i></span> Tous les services</a></li>
          </ul>
        </li>
        <li class="gel-nav-item">
          <a href="/a-propos" class="gel-nav-link">À propos <i class="bi-chevron-down chevron"></i></a>
          <ul class="gel-dropdown">
            <li><a href="/notre-cabinet"><span class="drop-icon"><i class="bi-building"></i></span> Notre Cabinet</a></li>
            <li><a href="/notre-equipe"><span class="drop-icon"><i class="bi-people-fill"></i></span> Notre Équipe</a></li>
            <li><a href="/carrieres"><span class="drop-icon"><i class="bi-briefcase-fill"></i></span> Carrières</a></li>
          </ul>
        </li>
        <li class="gel-nav-item">
          <a href="/ressources" class="gel-nav-link">Ressources <i class="bi-chevron-down chevron"></i></a>
          <ul class="gel-dropdown">
            <li><a href="/blogue"><span class="drop-icon"><i class="bi-pencil-square"></i></span> Blogue</a></li>
            <li><a href="/documentation"><span class="drop-icon"><i class="bi-file-text"></i></span> Documentation</a></li>
            <li><a href="/faq"><span class="drop-icon"><i class="bi-question-circle"></i></span> FAQ</a></li>
            <li><hr class="gel-dropdown-divider"></li>
            <li><a href="/centre-aide"><span class="drop-icon"><i class="bi-headset"></i></span> Centre d'aide</a></li>
          </ul>
        </li>
        <li class="gel-nav-item"><a href="/tarifs" class="gel-nav-link">Tarifs</a></li>
        <li class="gel-nav-item"><a href="/contact" class="gel-nav-link">Contact</a></li>
      </ul>

      <!-- Actions -->
      <div class="gel-nav-right">
        <template v-if="isRegisterPage">
          <a href="/login" class="gel-btn-nav gel-btn-nav-primary">
            <i class="bi-box-arrow-in-right"></i> Se connecter
          </a>
        </template>
        <template v-else>
          <a href="/register" class="gel-btn-nav gel-btn-nav-outline">
            <i class="bi-person-plus"></i> S'inscrire
          </a>
        </template>
        <button class="gel-toggler" @click="mobileOpen = !mobileOpen" aria-label="Menu">
          <i :class="mobileOpen ? 'bi-x-lg' : 'bi-list'"></i>
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div :class="['gel-mobile-menu', { open: mobileOpen }]">
      <a href="/" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-house text-orange me-2"></i>Accueil</a>
      <a href="/nos-modules" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-grid-3x3-gap text-orange me-2"></i>Nos Modules</a>
      <a href="/nos-services" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-grid-3x3-gap text-orange me-2"></i>Services</a>
      <div style="padding-left:36px;font-size:12px;color:rgba(30,41,59,0.7);margin-bottom:4px;">
        <a href="/services/comptabilite" class="gel-mobile-sub-link" @click="mobileOpen = false">Comptabilité</a>
        <a href="/services/fiscal" class="gel-mobile-sub-link" @click="mobileOpen = false">Fiscal</a>
        <a href="/services/juridique" class="gel-mobile-sub-link" @click="mobileOpen = false">Juridique</a>
        <a href="/services/social-paie" class="gel-mobile-sub-link" @click="mobileOpen = false">Social & Paie</a>
      </div>
      <a href="/blogue" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-pencil-square text-orange me-2"></i>Blogue</a>
      <a href="/documentation" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-file-text text-orange me-2"></i>Documentation</a>
      <a href="/faq" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-question-circle text-orange me-2"></i>FAQ</a>
      <a href="/centre-aide" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-headset text-orange me-2"></i>Centre d'aide</a>
      <a href="/notre-cabinet" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-building text-orange me-2"></i>Notre Cabinet</a>
      <a href="/notre-equipe" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-people-fill text-orange me-2"></i>Notre Équipe</a>
      <a href="/carrieres" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-briefcase-fill text-orange me-2"></i>Carrières</a>
      <a href="/tarifs" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-currency-dollar text-orange me-2"></i>Tarifs</a>
      <a href="/contact" class="gel-mobile-link" @click="mobileOpen = false"><i class="bi-envelope text-orange me-2"></i>Contact</a>

      <hr class="gel-mobile-divider" />
      <template v-if="isRegisterPage">
        <a href="/login" class="gel-mobile-btn" @click="mobileOpen = false">
          <i class="bi-box-arrow-in-right"></i> Se connecter
        </a>
      </template>
      <template v-else>
        <a href="/register" class="gel-mobile-btn" @click="mobileOpen = false">
          <i class="bi-person-plus"></i> S'inscrire
        </a>
      </template>
    </div>

    <div v-if="mobileOpen" class="sv-overlay" @click="mobileOpen = false"></div>
  </header>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  isRegisterPage: { type: Boolean, default: false },
});

const mobileOpen = ref(false);
</script>

<style scoped>
/* ── Variables internes ── */
:root {
  --sv-orange: #FF7900;
  --sv-orange-hov: #e06700;
  --sv-dark: #0F172A;
  --sv-gray: #64748B;
  --sv-light: #F8FAFC;
  --sv-border: #E2E8F0;
  --sv-text: #1E293B;
}

/* ── Navbar ── */
.gel-navbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 1050;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(0,0,0,0.06);
  height: 72px;
  display: flex; align-items: center;
  transition: border-color 0.25s, box-shadow 0.25s;
}

.gel-navbar .container-fluid {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 32px; max-width: 1320px; margin: 0 auto; width: 100%;
}

/* ── Logo ── */
.gel-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; flex-shrink: 0; }
.gel-brand-logo {
  width: 38px; height: 38px; background: var(--sv-orange); border-radius: 4px;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 900; color: #fff;
  letter-spacing: -0.5px;
}
.gel-brand-text { display: flex; flex-direction: column; line-height: 1.1; }
.gel-brand-name { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 16px; color: #111827; letter-spacing: -0.3px; }
.gel-brand-sub { font-size: 8.5px; font-weight: 600; color: var(--sv-gray); text-transform: uppercase; letter-spacing: 0.1em; }

/* ── Navigation ── */
.gel-nav-center { display: flex; align-items: center; gap: 0; list-style: none; margin: 0; padding: 0; }
.gel-nav-item { position: relative; }
.gel-nav-link {
  display: flex; align-items: center; gap: 3px;
  padding: 7px 13px; font-size: 13px; font-weight: 500;
  color: #1E293B; text-decoration: none;
  border-radius: 4px;
  transition: color 0.25s, background 0.25s;
  white-space: nowrap;
}
.gel-nav-link:hover, .gel-nav-link.active { color: var(--sv-orange); background: rgba(255,121,0,0.06); }
.gel-nav-link .chevron { font-size: 10px; transition: transform 0.25s; }
.gel-nav-item:hover > a .chevron { transform: rotate(180deg); }

/* ── Dropdown ── */
.gel-dropdown {
  position: absolute; top: calc(100% + 8px); left: 50%;
  transform: translateX(-50%);
  background: #fff; border: 1px solid var(--sv-border);
  border-radius: 10px; box-shadow: 0 12px 40px rgba(0,0,0,0.08);
  padding: 6px; min-width: 220px;
  opacity: 0; visibility: hidden;
  transform: translateX(-50%) translateY(-8px);
  transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
  list-style: none;
}
.gel-nav-item:hover .gel-dropdown { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
.gel-dropdown li a {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 12px; font-size: 13px; font-weight: 500;
  color: #1E293B; text-decoration: none;
  border-radius: 4px;
  transition: background 0.25s, color 0.25s;
}
.gel-dropdown li a:hover { background: rgba(255,121,0,0.06); color: var(--sv-orange); }
.drop-icon {
  width: 26px; height: 26px; background: #F1F5F9;
  border-radius: 4px; display: flex; align-items: center; justify-content: center;
  color: var(--sv-orange); font-size: 12px; flex-shrink: 0;
}
.gel-dropdown-divider { border: none; border-top: 1px solid var(--sv-border); margin: 4px 0; }

/* ── Boutons ── */
.gel-nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.gel-btn-nav {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 7px 16px; font-size: 12.5px; font-weight: 600;
  border-radius: 6px; text-decoration: none;
  transition: all 0.25s; border: none; cursor: pointer;
}
.gel-btn-nav-primary { background: var(--sv-orange); color: #fff; }
.gel-btn-nav-primary:hover { background: var(--sv-orange-hov); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(255,121,0,0.3); }
.gel-btn-nav-outline { background: transparent; color: #1E293B; border: 1.5px solid var(--sv-border); }
.gel-btn-nav-outline:hover { border-color: var(--sv-orange); color: var(--sv-orange); }

/* ── Toggler ── */
.gel-toggler { display: none; background: none; border: 1.5px solid var(--sv-border); border-radius: 4px; padding: 6px 9px; cursor: pointer; color: #111827; font-size: 17px; transition: all 0.25s; }
.gel-toggler:hover { border-color: var(--sv-orange); color: var(--sv-orange); }

/* ── Mobile Menu ── */
.gel-mobile-menu {
  display: none; position: fixed; top: 72px; left: 0; right: 0;
  background: #fff; border-bottom: 3px solid var(--sv-orange);
  box-shadow: 0 8px 30px rgba(0,0,0,0.1); z-index: 1040;
  padding: 16px 24px 24px;
  max-height: calc(100vh - 72px); overflow-y: auto;
}
.gel-mobile-menu.open { display: block; }
.gel-mobile-link {
  display: flex; align-items: center; gap: 10px;
  padding: 11px 0; font-size: 14px; font-weight: 500;
  color: #1E293B; text-decoration: none;
  border-bottom: 1px solid var(--sv-border);
}
.gel-mobile-link:last-child { border-bottom: none; }
.gel-mobile-link:hover { color: var(--sv-orange); }
.gel-mobile-sub-link {
  display: block;
  padding: 6px 0;
  font-size: 13px;
  color: rgba(30,41,59,0.7);
  text-decoration: none;
}
.gel-mobile-sub-link:hover { color: var(--sv-orange); }
.gel-mobile-divider {
  border: none;
  border-top: 1px solid var(--sv-border);
  margin: 12px 0;
}
.gel-mobile-btn {
  display: flex; align-items: center; gap: 6px;
  padding: 11px 0; font-size: 14px; font-weight: 600;
  color: var(--sv-orange); text-decoration: none;
  border-bottom: 1px solid var(--sv-border);
}
.gel-mobile-btn:hover { color: var(--sv-orange-hov); }

.text-orange { color: var(--sv-orange); }

/* ── Overlay ── */
.sv-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.2); z-index: 1039; }

/* ── Responsive ── */
@media (max-width: 991px) {
  .gel-nav-center { display: none; }
  .gel-toggler { display: flex; }
  .gel-navbar .container-fluid { padding: 0 16px; }
}
</style>
