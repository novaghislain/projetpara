<script setup>
import { ref } from 'vue';
import { 
    LayoutDashboard, 
    ShoppingBag, 
    Users, 
    Settings, 
    LogOut, 
    Menu, 
    X,
    Bell,
    User
} from 'lucide-vue-next';

const isSidebarOpen = ref(true);

const menuItems = [
    { name: 'Tableau de bord', icon: LayoutDashboard, href: '/admin' },
    { name: 'Commandes', icon: ShoppingBag, href: '/admin/orders' },
    { name: 'Clients', icon: Users, href: '/admin/users' },
    { name: 'Paramètres', icon: Settings, href: '/admin/settings' },
];

const logout = () => {
    // Implement logout logic
    window.location.href = '/';
};
</script>

<template>
    <div class="admin-layout min-h-screen bg-light d-flex">
        <!-- Sidebar -->
        <aside 
            class="sidebar bg-primary text-white transition-all shadow-lg"
            :class="isSidebarOpen ? 'opened' : 'closed'"
        >
            <div class="p-4 d-flex align-items-center justify-content-between border-bottom border-white border-opacity-10">
                <div v-if="isSidebarOpen" class="fw-bold fs-5">Victoire Admin</div>
                <button @click="isSidebarOpen = !isSidebarOpen" class="btn btn-link text-white p-0">
                    <X v-if="isSidebarOpen" :size="20" />
                    <Menu v-else :size="20" />
                </button>
            </div>

            <nav class="p-3">
                <ul class="list-unstyled">
                    <li v-for="item in menuItems" :key="item.href" class="mb-2">
                        <a 
                            :href="item.href" 
                            class="nav-link d-flex align-items-center gap-3 p-3 rounded-3 text-white transition-all hover-bg-white hover-bg-opacity-10"
                        >
                            <component :is="item.icon" :size="20" />
                            <span v-if="isSidebarOpen">{{ item.name }}</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="mt-auto p-3 border-top border-white border-opacity-10">
                <button @click="logout" class="nav-link d-flex align-items-center gap-3 p-3 rounded-3 text-white w-100 border-0 bg-transparent hover-bg-danger transition-all">
                    <LogOut :size="20" />
                    <span v-if="isSidebarOpen">Déconnexion</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm py-3 px-4 d-flex align-items-center justify-content-between z-1">
                <h4 class="fw-bold mb-0">Interface d'Administration</h4>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link text-dark position-relative p-2">
                        <Bell :size="20" />
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 8px">
                            3
                        </span>
                    </button>
                    <div class="d-flex align-items-center gap-2 border-start ps-3 border-light">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px">
                            <User :size="18" />
                        </div>
                        <span class="small fw-bold d-none d-sm-inline">Admin Victoire</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 overflow-auto flex-grow-1">
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<style scoped>
.sidebar {
    height: 100vh;
    display: flex;
    flex-direction: column;
}
.sidebar.opened { width: 260px; }
.sidebar.closed { width: 80px; }
.sidebar.closed .nav-link { justify-content: center; padding: 1rem 0; }
.hover-bg-white:hover { background-color: rgba(255, 255, 255, 0.1); }
.hover-bg-danger:hover { background-color: #dc3545; }
.transition-all { transition: all 0.3s ease-in-out; }
</style>
