<script setup>
import { ShoppingCart, Search, Menu, User, LogIn, LogOut } from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';
import { authStore } from '../stores/auth';
import { cartStore, fetchCart } from '../stores/cart';

const pathname = ref('');

onMounted(() => {
    pathname.value = window.location.pathname;
    // Fetch cart from session
    fetchCart();
});

const user = computed(() => authStore.user);

const navLinks = [
    { name: 'Accueil', href: '/' },
    { name: 'Boutique', href: '/shop' },
    { name: 'Services', href: '/services' },
    { name: 'Consultation', href: '/consultation' },
    { name: 'Contact', href: '/contact' }
];

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top border-bottom border-white border-opacity-10 bg-primary shadow-lg py-2">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="/">
                <div class="bg-white rounded-3 d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 160px; height: 65px">
                    <img src="/images/products/official_logo_victoire.png" alt="Victoire Logo" class="w-100 h-100 object-fit-contain p-1" />
                </div>
            </a>

            <!-- Mobile Toggle & Right -->
            <div class="d-flex align-items-center gap-2 order-lg-3">
                <button class="btn btn-link text-white p-0 d-none d-sm-inline-block">
                    <Search :size="20" />
                </button>
                <a href="/cart" class="position-relative cursor-pointer text-white text-decoration-none">
                    <ShoppingCart :size="22" />
                    <span v-if="cartStore.count > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-gold text-white" style="font-size: 8px">
                        {{ cartStore.count }}
                    </span>
                </a>
                <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                    <Menu :size="24" class="text-white" />
                </button>
            </div>

            <!-- Links -->
            <div class="collapse navbar-collapse order-lg-2" id="navContent">
                <ul class="navbar-nav mx-auto gap-lg-4 mt-3 mt-lg-0">
                    <li v-for="link in navLinks" :key="link.href" class="nav-item">
                        <a
                            class="nav-link fw-bold transition-all position-relative px-0"
                            :class="pathname === link.href ? 'text-white active' : 'text-white text-opacity-75 hover-text-gold'"
                            :href="link.href"
                        >
                            {{ link.name }}
                            <span v-if="pathname === link.href" class="position-absolute bottom-0 start-0 w-100 bg-gold" style="height: 2px"></span>
                        </a>
                    </li>
                </ul>
                <!-- Auth Links -->
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0 ms-lg-3 border-top border-white border-opacity-10 pt-3 pt-lg-0 border-lg-0 pt-lg-0">
                    <template v-if="user">
                        <span class="text-white text-opacity-75 small me-2 d-none d-md-inline">
                            <User :size="14" class="me-1" />{{ user.name || user.email }}
                        </span>
                        <form method="POST" action="/logout" class="d-inline">
                            <input type="hidden" name="_token" :value="csrfToken" />
                            <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-3 d-flex align-items-center gap-1">
                                <LogOut :size="14" /> Deconnexion
                            </button>
                        </form>
                    </template>
                    <template v-else>
                        <a href="/login" class="btn btn-sm btn-outline-light rounded-pill px-3 d-flex align-items-center gap-1">
                            <LogIn :size="14" /> Connexion
                        </a>
                        <a href="/register" class="btn btn-sm btn-gold text-white rounded-pill px-3 d-flex align-items-center gap-1 fw-bold">
                            <User :size="14" /> Inscription
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.hover-text-gold:hover { color: var(--accent-gold) !important; }
.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: var(--accent-gold);
}
.btn-gold {
    background: linear-gradient(135deg, #c9a84c, #d4af37);
    border: none;
}
.btn-gold:hover {
    background: linear-gradient(135deg, #d4af37, #e6c25a);
}
</style>
