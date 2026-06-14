<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const notifications = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const filterType = ref('');
const filterStatus = ref('');

const typeOptions = [
    { value: '', label: 'Tous les types' },
    { value: 'info', label: 'Information' },
    { value: 'success', label: 'Succès' },
    { value: 'warning', label: 'Avertissement' },
    { value: 'error', label: 'Erreur' },
];

const statusOptions = [
    { value: '', label: 'Tous' },
    { value: 'unread', label: 'Non lues' },
    { value: 'read', label: 'Lues' },
];

function notifIcon(type) {
    const icons = {
        info: 'bi-info-circle text-primary',
        success: 'bi-check-circle text-success',
        warning: 'bi-exclamation-triangle text-warning',
        error: 'bi-x-circle text-danger',
    };
    return 'bi ' + (icons[type] || 'bi-bell text-secondary');
}

function notifBadge(type) {
    const badges = {
        info: 'bg-primary',
        success: 'bg-success',
        warning: 'bg-warning text-dark',
        error: 'bg-danger',
    };
    return badges[type] || 'bg-secondary';
}

function relativeDate(dateStr) {
    if (!dateStr) return '';
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return "à l'instant";
    if (mins < 60) return 'il y a ' + mins + ' min';
    const hours = Math.floor(mins / 60);
    if (hours < 24) return 'il y a ' + hours + ' h';
    const days = Math.floor(hours / 24);
    return 'il y a ' + days + ' j';
}

async function fetchNotifications() {
    loading.value = true;
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    const params = new URLSearchParams();
    params.set('page', currentPage.value);
    params.set('per_page', 15);
    if (filterType.value) params.set('type', filterType.value);
    if (filterStatus.value) params.set('status', filterStatus.value);

    try {
        const res = await fetch('/api/company/notifications?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
        });
        const data = await res.json();
        notifications.value = data.notifications || [];
        currentPage.value = data.pagination?.current_page || 1;
        lastPage.value = data.pagination?.last_page || 1;
        total.value = data.pagination?.total || 0;
    } catch (e) {
        console.error('Error fetching notifications', e);
    } finally {
        loading.value = false;
    }
}

async function markAsRead(n) {
    if (n.read_at) return;
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        await fetch('/api/company/notifications/' + n.id + '/read', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': token },
        });
        n.read_at = new Date().toISOString();
    } catch (e) {
        console.error('Mark read error', e);
    }
}

async function markAllAsRead() {
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        await fetch('/api/company/notifications/read-all', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': token },
        });
        notifications.value.forEach(n => {
            n.read_at = n.read_at || new Date().toISOString();
        });
    } catch (e) {
        console.error('Mark all read error', e);
    }
}

async function destroy(n) {
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        await fetch('/api/company/notifications/' + n.id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': token },
        });
        notifications.value = notifications.value.filter(item => item.id !== n.id);
        total.value = Math.max(0, total.value - 1);
    } catch (e) {
        console.error('Delete error', e);
    }
}

function goToPage(page) {
    if (page < 1 || page > lastPage.value) return;
    currentPage.value = page;
    fetchNotifications();
}

function resetFilters() {
    filterType.value = '';
    filterStatus.value = '';
    currentPage.value = 1;
    fetchNotifications();
}

onMounted(() => {
    fetchNotifications();
});
</script>

<template>
    <CompanyLayout page-title="Notifications">
        <div class="container-fluid px-0">
            <!-- Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Notifications</h4>
                    <p class="text-muted small mb-0">
                        {{ total }} notification{{ total > 1 ? 's' : '' }} au total
                    </p>
                </div>
                <button
                    v-if="notifications.some(n => !n.read_at)"
                    @click="markAllAsRead"
                    class="btn btn-outline-primary btn-sm rounded-pill px-3"
                >
                    <i class="bi-check-all me-1"></i> Tout marquer comme lu
                </button>
            </div>

            <!-- Filters -->
            <div class="row g-2 mb-4">
                <div class="col-auto">
                    <select v-model="filterType" @change="currentPage=1; fetchNotifications()"
                            class="form-select form-select-sm" style="min-width: 180px;">
                        <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>
                <div class="col-auto">
                    <select v-model="filterStatus" @change="currentPage=1; fetchNotifications()"
                            class="form-select form-select-sm" style="min-width: 140px;">
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>
                <div class="col-auto" v-if="filterType || filterStatus">
                    <button @click="resetFilters" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi-x-circle me-1"></i> Effacer
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <!-- Empty -->
            <div v-else-if="notifications.length === 0" class="text-center py-5">
                <i class="bi-bell-slash" style="font-size: 48px; color: #ccc;"></i>
                <p class="text-muted mt-3 mb-0">Aucune notification trouvée</p>
            </div>

            <!-- Notifications List -->
            <div v-else class="list-group rounded-3 shadow-sm">
                <div
                    v-for="n in notifications"
                    :key="n.id"
                    class="list-group-item list-group-item-action d-flex gap-3 align-items-start border-0 border-bottom"
                    :class="{ 'bg-light': !n.read_at }"
                    style="border-radius: 0;"
                >
                    <!-- Icon -->
                    <div class="mt-1">
                        <i :class="notifIcon(n.type)" style="font-size: 20px;"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-semibold small">{{ n.title }}</span>
                                <span class="badge rounded-pill ms-2" :class="notifBadge(n.type)" style="font-size: 9px;">
                                    {{ n.type }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <!-- Mark as read -->
                                <button
                                    v-if="!n.read_at"
                                    @click="markAsRead(n)"
                                    class="btn btn-sm btn-link text-decoration-none text-muted p-1"
                                    title="Marquer comme lue"
                                >
                                    <i class="bi-check-circle" style="font-size: 14px;"></i>
                                </button>
                                <!-- Delete -->
                                <button
                                    @click="destroy(n)"
                                    class="btn btn-sm btn-link text-decoration-none text-danger p-1"
                                    title="Supprimer"
                                >
                                    <i class="bi-trash" style="font-size: 14px;"></i>
                                </button>
                            </div>
                        </div>
                        <p class="mb-1 small text-muted">{{ n.message }}</p>
                        <div class="text-muted" style="font-size: 10px;">
                            {{ relativeDate(n.created_at) }}
                            <span v-if="n.read_at" class="ms-2 text-success">
                                <i class="bi-check-circle-fill" style="font-size: 9px;"></i> Lue
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <nav v-if="lastPage > 1" class="mt-4">
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item" :class="{ disabled: currentPage <= 1 }">
                        <button class="page-link" @click="goToPage(currentPage - 1)">
                            <i class="bi-chevron-left"></i>
                        </button>
                    </li>
                    <li
                        v-for="page in lastPage"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === currentPage }"
                    >
                        <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage >= lastPage }">
                        <button class="page-link" @click="goToPage(currentPage + 1)">
                            <i class="bi-chevron-right"></i>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </CompanyLayout>
</template>
