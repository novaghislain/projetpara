<script setup>
import { ref, computed, onMounted } from 'vue';
import { authStore } from '../../stores/auth';

const props = defineProps({
    pageTitle: { type: String, default: 'Mon Espace' }
});

const company = ref(null);
const licenses = ref([]);
const loading = ref(true);

const userInitial = computed(() => {
    return authStore.user?.name?.charAt(0).toUpperCase() || 'E';
});

const sidebarLinks = computed(() => {
    const links = [
        { name: 'Tableau de bord', icon: 'bi-speedometer2', route: '/company/dashboard', key: 'company-dashboard' },
        { name: 'Mes Services', icon: 'bi-grid-3x3-gap', route: '/company/services', key: 'company-services' },
        { name: 'Documents (GED)', icon: 'bi-folder2-open', route: '/company/ged', key: 'company-ged' },
        { name: 'Comptabilite', icon: 'bi-calculator', route: '/company/accounting', key: 'company-accounting' },
        { name: 'Mon Profil', icon: 'bi-person', route: '/company/profile', key: 'company-profile' },
    ];

    // Only company admins can manage users
    if (authStore.user?.is_company_admin) {
        links.splice(2, 0, { name: 'Utilisateurs', icon: 'bi-people', route: '/company/users', key: 'company-users' });
    }

    return links;
});

const pageKey = computed(() => {
    const path = window.location.pathname;
    if (path.startsWith('/company/dashboard') || path === '/company') return 'company-dashboard';
    if (path.startsWith('/company/services')) return 'company-services';
    if (path.startsWith('/company/users')) return 'company-users';
    if (path.startsWith('/company/profile')) return 'company-profile';
    if (path.startsWith('/company/ged')) return 'company-ged';
    if (path.startsWith('/company/accounting')) return 'company-accounting';
    return 'company-dashboard';
});

const isSidebarCollapsed = ref(false);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const logout = async () => {
    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        const res = await fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });
        if (res.redirected || res.ok) {
            window.location.href = '/login';
        }
    } catch {
        const form = document.getElementById('logout-form');
        const input = document.getElementById('logout-csrf-token');
        if (input && csrfToken) input.value = csrfToken;
        if (form) form.submit();
    }
};

onMounted(async () => {
    const clientId = window.__CLIENT_ID__;
    if (!clientId) { loading.value = false; return; }
    try {
        const res = await fetch(`/api/company/${clientId}/info`);
        const data = await res.json();
        company.value = data.company;
        licenses.value = data.licenses;
    } catch (e) {
        console.error('Erreur chargement entreprise', e);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="d-flex" style="min-height: 100vh;">
        <!-- Sidebar -->
        <nav class="sidebar d-flex flex-column flex-shrink-0"
             :style="{ width: isSidebarCollapsed ? '70px' : '270px', transition: 'width 0.3s' }">
            <!-- Logo / Company Name -->
            <div class="p-3 d-flex align-items-center gap-2 border-bottom border-white border-opacity-10"
                 :class="{ 'justify-content-center': isSidebarCollapsed }">
                <div class="bg-white bg-opacity-20 rounded-2 d-flex align-items-center justify-content-center"
                     style="width: 36px; height: 36px; flex-shrink: 0;">
                    <i class="bi-building text-white" style="font-size: 20px;"></i>
                </div>
                <div v-if="!isSidebarCollapsed" class="text-truncate">
                    <div class="fw-bold text-white font-heading small">{{ company?.company_name || 'Mon Entreprise' }}</div>
                    <div class="text-white-50 small" style="font-size: 10px;">Espace Client</div>
                </div>
            </div>

            <!-- Navigation -->
            <ul class="nav flex-column mt-3 flex-grow-1">
                <li v-for="link in sidebarLinks" :key="link.key">
                    <a :href="link.route"
                       class="nav-link d-flex align-items-center gap-2"
                       :class="{ active: pageKey === link.key }"
                       :title="isSidebarCollapsed ? link.name : ''">
                        <i :class="link.icon" style="font-size: 18px; width: 22px;"></i>
                        <span v-if="!isSidebarCollapsed">{{ link.name }}</span>
                    </a>
                </li>
            </ul>

            <!-- Licensed Services Summary -->
            <div v-if="!isSidebarCollapsed && licenses.length" class="px-3 py-2">
                <span class="small text-white-50 text-uppercase fw-bold" style="font-size:10px;letter-spacing:.1em">
                    <i class="bi-key me-1"></i> Services sous licence
                </span>
            </div>
            <ul v-if="!isSidebarCollapsed" class="nav flex-column mb-2">
                <li v-for="lic in licenses" :key="lic.id">
                    <span class="nav-link d-flex align-items-center gap-2 text-white-50 small"
                          style="cursor: default; opacity: 0.8; font-size: 13px;">
                        <i class="bi-check-circle-fill"
                           :class="lic.valid ? 'text-success' : 'text-warning'"
                           style="font-size: 12px;"></i>
                        {{ lic.service_name }}
                        <span v-if="!lic.valid" class="badge bg-warning text-dark ms-auto" style="font-size: 9px;">Expiré</span>
                    </span>
                </li>
            </ul>

            <!-- Sidebar Footer -->
            <div class="p-3 border-top border-white border-opacity-10">
                <button @click="toggleSidebar" class="btn btn-sm text-white-50 w-100 text-start"
                        :title="isSidebarCollapsed ? 'Étendre' : 'Réduire'">
                    <i :class="isSidebarCollapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
                    <span v-if="!isSidebarCollapsed" class="ms-2 small">Réduire</span>
                </button>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="d-flex flex-column flex-grow-1" style="min-width: 0;">
            <!-- Header -->
            <header class="bg-white border-bottom px-4 py-2 d-flex align-items-center justify-content-between"
                     style="height: 64px;">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0 fw-bold font-heading">{{ pageTitle }}</h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 rounded-pill"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 32px; height: 32px; font-size: 14px; font-weight: 600;">
                                {{ userInitial }}
                            </div>
                            <div class="text-start d-none d-md-block">
                                <div class="small fw-semibold">{{ authStore.user?.name || 'Utilisateur' }}</div>
                                <div class="small text-muted">{{ authStore.user?.role_name || 'Utilisateur' }}</div>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0">
                            <li><a class="dropdown-item" href="/company/profile"><i class="bi-person me-2"></i>Mon Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="#" class="dropdown-item text-danger" @click.prevent="logout">
                                    <i class="bi-box-arrow-right me-2"></i>Déconnexion
                                </a>
                                <form id="logout-form" method="POST" action="/logout" style="display: none;">
                                    <input type="hidden" name="_token" id="logout-csrf-token">
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 p-4" style="overflow-y: auto; background: #f5f7fa;">
                <slot />
            </main>
        </div>
    </div>
</template>
