<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { authStore } from '../stores/auth';

const props = defineProps({
    pageTitle: { type: String, default: 'Mon Espace' }
});

const company = ref(null);
const licenses = ref([]);
const loading = ref(true);

// ─── Notification state ──────────────────────────────────────
const unreadCount = ref(0);
const recentNotifications = ref([]);
const showNotificationsDropdown = ref(false);

const relativeDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now - date;
    const mins = Math.floor(diffMs / 60000);
    const hours = Math.floor(diffMs / 3600000);
    const days = Math.floor(diffMs / 86400000);
    if (mins < 1) return "A l'instant";
    if (mins < 60) return `${mins} min`;
    if (hours < 24) return `${hours}h`;
    if (days < 7) return `${days}j`;
    return date.toLocaleDateString('fr-FR');
};

const fetchUnreadCount = async () => {
    try {
        const res = await fetch('/api/company/notifications/unread-count');
        const data = await res.json();
        unreadCount.value = data.count;
    } catch (e) {
        // Silently fail on polling errors
    }
};

const fetchRecentNotifications = async () => {
    try {
        const res = await fetch('/api/company/notifications?limit=5');
        const data = await res.json();
        recentNotifications.value = data.data || data;
    } catch (e) {
        // Silently fail
    }
};

const markAsRead = async (id) => {
    try {
        await fetch(`/api/company/notifications/${id}/read`, { method: 'PATCH' });
        fetchRecentNotifications();
        fetchUnreadCount();
    } catch (e) {}
};

const markAllAsRead = async () => {
    try {
        await fetch('/api/company/notifications/read-all', { method: 'PATCH' });
        recentNotifications.value.forEach(n => { n.read_at = new Date().toISOString(); });
        unreadCount.value = 0;
    } catch (e) {}
};

const toggleNotifications = () => {
    showNotificationsDropdown.value = !showNotificationsDropdown.value;
    if (showNotificationsDropdown.value) {
        fetchRecentNotifications();
    }
};

let pollingInterval;

// ─── User initial ────────────────────────────────────────────

const userInitial = computed(() => {
    return authStore.user?.name?.charAt(0).toUpperCase() || 'E';
});

const sidebarLinks = computed(() => {
    const links = [
        { name: 'Tableau de bord', icon: 'bi-speedometer2', route: '/company/dashboard', key: 'company-dashboard' },
        { name: 'Mes Services', icon: 'bi-grid-3x3-gap', route: '/company/services', key: 'company-services' },
        { name: 'Notifications', icon: 'bi-bell', route: '/company/notifications', key: 'company-notifications' },
        { name: 'Documents (GED)', icon: 'bi-folder2-open', route: '/company/ged', key: 'company-ged' },
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
    if (path.startsWith('/company/notifications')) return 'company-notifications';
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

    // Notification polling
    fetchUnreadCount();
    fetchRecentNotifications();
    pollingInterval = setInterval(() => {
        fetchUnreadCount();
    }, 30000);
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
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
                    <!-- Notification Bell -->
                    <div class="dropdown" @click.self="showNotificationsDropdown = false">
                        <button class="btn btn-light position-relative rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px;"
                                @click="toggleNotifications"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                aria-label="Notifications">
                            <i class="bi-bell" style="font-size: 18px;"></i>
                            <span v-if="unreadCount > 0"
                                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                  style="font-size: 10px; min-width: 18px;">
                                {{ unreadCount > 99 ? '99+' : unreadCount }}
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0 p-0"
                             style="width: 360px; max-height: 480px; overflow-y: auto;"
                             :class="{ show: showNotificationsDropdown }">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                                <h6 class="fw-bold mb-0">Notifications</h6>
                                <button v-if="unreadCount > 0"
                                        class="btn btn-sm btn-link text-decoration-none p-0"
                                        @click="markAllAsRead">
                                    Tout marquer lu
                                </button>
                            </div>
                            <div v-if="recentNotifications.length === 0" class="text-center py-4 text-muted">
                                <i class="bi-bell-slash" style="font-size: 1.5rem;"></i>
                                <p class="small mt-2 mb-0">Aucune notification</p>
                            </div>
                            <div v-else>
                                <div v-for="notif in recentNotifications" :key="notif.id"
                                     class="px-3 py-2 border-bottom notification-item"
                                     :class="{ 'bg-light': !notif.read_at }"
                                     style="cursor: pointer;">
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="badge rounded-pill mt-1 flex-shrink-0"
                                              :class="'bg-' + (notif.type === 'error' ? 'danger' : notif.type || 'info')"
                                              style="font-size: 8px; width: 8px; height: 8px; padding: 0;">
                                            &nbsp;
                                        </span>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <strong class="small text-dark">{{ notif.title }}</strong>
                                                <span class="small text-muted flex-shrink-0 ms-2">{{ relativeDate(notif.created_at) }}</span>
                                            </div>
                                            <p class="small text-muted mb-1 text-truncate">{{ notif.message }}</p>
                                            <button v-if="!notif.read_at"
                                                    class="btn btn-sm btn-outline-primary py-0 px-2"
                                                    style="font-size: 11px;"
                                                    @click.stop="markAsRead(notif.id)">
                                                Marquer lue
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="/company/notifications"
                               class="dropdown-item text-center small fw-semibold py-2 border-top"
                               style="background: #f8f9fa;">
                                Voir toutes les notifications
                            </a>
                        </div>
                    </div>

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

<style scoped>
.notification-item:hover {
    background: #f0f4ff !important;
}
</style>
