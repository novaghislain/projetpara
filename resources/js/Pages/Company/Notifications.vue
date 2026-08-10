<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const notifications = ref([]);
const loading = ref(true);
const meta = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });
const filterType = ref('');
const filterStatus = ref('');

const fetchNotifications = async (page = 1) => {
    loading.value = true;
    try {
        let url = `/api/company/notifications?page=${page}`;
        if (filterType.value) url += `&type=${filterType.value}`;
        if (filterStatus.value) url += `&status=${filterStatus.value}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error('Erreur serveur');
        const data = await res.json();
        notifications.value = data.data || [];
        meta.value = {
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            total: data.total || 0,
            from: data.from || 0,
            to: data.to || 0,
        };
    } catch (e) {
        console.error('Erreur chargement notifications', e);
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (id) => {
    try {
        await fetch(`/api/company/notifications/${id}/read`, { method: 'PATCH' });
        fetchNotifications(meta.value.current_page);
    } catch (e) {
        console.error('Erreur', e);
    }
};

const markAllAsRead = async () => {
    if (notifications.value.length === 0) return;
    try {
        await fetch('/api/company/notifications/read-all', { method: 'PATCH' });
        fetchNotifications(meta.value.current_page);
    } catch (e) {
        console.error('Erreur', e);
    }
};

const destroy = async (id) => {
    if (!confirm('Supprimer cette notification ?')) return;
    try {
        await fetch(`/api/company/notifications/${id}`, { method: 'DELETE' });
        fetchNotifications(meta.value.current_page);
    } catch (e) {
        console.error('Erreur', e);
    }
};

const typeBadgeClass = (type) => {
    const map = { info: 'bg-primary', success: 'bg-success', warning: 'bg-warning text-dark', error: 'bg-danger' };
    return map[type] || 'bg-secondary';
};

const typeIcon = (type) => {
    const map = { info: 'bi-info-circle', success: 'bi-check-circle', warning: 'bi-exclamation-triangle', error: 'bi-x-circle' };
    return map[type] || 'bi-bell';
};

const relativeDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now - date;
    const mins = Math.floor(diffMs / 60000);
    const hours = Math.floor(diffMs / 3600000);
    const days = Math.floor(diffMs / 86400000);
    if (mins < 1) return "À l'instant";
    if (mins < 60) return `Il y a ${mins} min`;
    if (hours < 24) return `Il y a ${hours}h`;
    if (days < 7) return `Il y a ${days}j`;
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
};

const pageNumbers = computed(() => {
    const pages = [];
    const last = meta.value.last_page;
    const current = meta.value.current_page;
    const start = Math.max(1, current - 2);
    const end = Math.min(last, current + 2);
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
});

watch([filterType, filterStatus], () => {
    fetchNotifications(1);
});

onMounted(() => {
    fetchNotifications(1);
});
</script>

<template>
    <CompanyLayout page-title="Notifications">
        <div class="container-fluid px-0">
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="fw-bold font-heading mb-1">Notifications</h4>
                    <p class="text-muted small mb-0">
                        {{ meta.total }} notification{{ meta.total !== 1 ? 's' : '' }}
                        <span v-if="meta.total > 0">({{ meta.from }}-{{ meta.to }})</span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3"
                            @click="markAllAsRead"
                            :disabled="notifications.length === 0">
                        <i class="bi-check-all me-1"></i>Tout marquer comme lu
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border-0 rounded-3 shadow-sm mb-4">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Type</label>
                            <select class="form-select form-select-sm" v-model="filterType">
                                <option value="">Tous les types</option>
                                <option value="info">Info</option>
                                <option value="success">Succès</option>
                                <option value="warning">Avertissement</option>
                                <option value="error">Erreur</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Statut</label>
                            <select class="form-select form-select-sm" v-model="filterStatus">
                                <option value="">Tous les statuts</option>
                                <option value="unread">Non lues</option>
                                <option value="read">Lues</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <span class="small text-muted">
                                <i class="bi-funnel me-1"></i>
                                {{ filterType || filterStatus ? 'Filtres actifs' : 'Aucun filtre' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <div v-else-if="notifications.length === 0" class="card border-0 rounded-3 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Aucune notification</h5>
                    <p class="text-muted small mb-0">
                        {{ filterType || filterStatus ? 'Essayez de modifier les filtres.' : 'Vous n\'avez pas encore de notifications.' }}
                    </p>
                </div>
            </div>

            <div v-else class="card border-0 rounded-3 shadow-sm">
                <div class="list-group list-group-flush">
                    <div v-for="notif in notifications" :key="notif.id"
                         class="list-group-item list-group-item-action p-3 p-md-4"
                         :class="{ 'bg-light bg-opacity-50': !notif.read_at }">
                        <div class="d-flex align-items-start gap-3">
                            <!-- Type Icon -->
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                 :class="typeBadgeClass(notif.type) + ' bg-opacity-10'"
                                 style="width: 40px; height: 40px;">
                                <i :class="typeIcon(notif.type) + ' fs-5'"
                                   :class="typeBadgeClass(notif.type).replace('bg-', 'text-')"></i>
                            </div>

                            <!-- Content -->
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-1">
                                    <div>
                                        <strong class="text-dark" :class="{ 'fw-bold': !notif.read_at }">
                                            {{ notif.title }}
                                        </strong>
                                        <span v-if="!notif.read_at"
                                              class="badge bg-primary rounded-pill ms-2"
                                              style="font-size: 9px; vertical-align: middle;">NOUVEAU</span>
                                    </div>
                                    <span class="small text-muted text-nowrap flex-shrink-0">
                                        <i class="bi-clock me-1"></i>{{ relativeDate(notif.created_at) }}
                                    </span>
                                </div>
                                <p class="mb-2 small text-muted mt-1">{{ notif.message }}</p>

                                <!-- Actions -->
                                <div class="d-flex gap-2">
                                    <button v-if="!notif.read_at"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                            @click="markAsRead(notif.id)">
                                        <i class="bi-check2 me-1"></i>Marquer lue
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                            @click="destroy(notif.id)">
                                        <i class="bi-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="meta.last_page > 1" class="card-footer bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <span class="small text-muted">
                        Page {{ meta.current_page }} sur {{ meta.last_page }}
                    </span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: meta.current_page <= 1 }">
                                <button class="page-link" @click="fetchNotifications(meta.current_page - 1)">
                                    <i class="bi-chevron-left"></i>
                                </button>
                            </li>
                            <li v-for="p in pageNumbers" :key="p" class="page-item"
                                :class="{ active: p === meta.current_page }">
                                <button class="page-link" @click="fetchNotifications(p)">{{ p }}</button>
                            </li>
                            <li class="page-item" :class="{ disabled: meta.current_page >= meta.last_page }">
                                <button class="page-link" @click="fetchNotifications(meta.current_page + 1)">
                                    <i class="bi-chevron-right"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
