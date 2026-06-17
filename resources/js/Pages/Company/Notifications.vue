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
        info: 'bi-info-circle',
        success: 'bi-check-circle',
        warning: 'bi-exclamation-triangle',
        error: 'bi-x-circle',
    };
    return 'bi ' + (icons[type] || 'bi-bell');
}

function relativeDate(dateStr) {
    if (!dateStr) return '';
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return "À l'instant";
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

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length);

onMounted(() => { fetchNotifications(); });
</script>

<template>
    <CompanyLayout page-title="Notifications">
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888;font-size:14px;">Chargement…</span>
        </div>

        <template v-else>
            <div class="isup-shell">
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo"><i class="bi-bell" style="font-size:20px;"></i></div>
                        <div>
                            <div class="isup-portal-company">Notifications</div>
                            <div class="isup-portal-sub">
                                {{ total }} notification{{ total > 1 ? 's' : '' }}
                                <span v-if="unreadCount"> · <span style="color:#FF7900;font-weight:700;">{{ unreadCount }} non lue{{ unreadCount>1?'s':'' }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <!-- Filtres + actions -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <select v-model="filterType" @change="currentPage=1; fetchNotifications()" class="isup-select" style="width:auto;min-width:160px;">
                                <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                            <select v-model="filterStatus" @change="currentPage=1; fetchNotifications()" class="isup-select" style="width:auto;min-width:140px;">
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                            </select>
                            <button v-if="filterType || filterStatus" class="isup-btn-outline" @click="resetFilters">
                                <i class="bi-x-circle me-1"></i> Effacer
                            </button>
                        </div>
                        <button v-if="unreadCount" class="isup-btn-outline" @click="markAllAsRead">
                            <i class="bi-check2-all me-1"></i> Tout marquer comme lu
                        </button>
                    </div>

                    <!-- Empty -->
                    <div v-if="notifications.length === 0" class="isup-empty-state">
                        <i class="bi-bell-slash" style="font-size:48px;color:#dce3ee;display:block;margin-bottom:12px;"></i>
                        <p style="font-size:14px;color:#888;">Aucune notification trouvée</p>
                    </div>

                    <!-- Liste -->
                    <div v-else class="isup-notif-list">
                        <div v-for="n in notifications" :key="n.id"
                             class="isup-notif-item" :class="{ 'isup-notif-unread': !n.read_at }"
                             @click="markAsRead(n)">
                            <div class="isup-notif-icon" :class="n.type === 'error' ? 'notif-icon-red' : n.type === 'warning' ? 'notif-icon-orange' : n.type === 'success' ? 'notif-icon-green' : 'notif-icon-blue'">
                                <i :class="notifIcon(n.type)"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="isup-notif-title">{{ n.title }}</span>
                                        <span class="isup-badge-notif" :class="'badge-' + n.type">{{ n.type }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-2">
                                        <button v-if="!n.read_at" class="isup-notif-action" title="Marquer comme lue" @click.stop="markAsRead(n)">
                                            <i class="bi-check-circle fs-6"></i>
                                        </button>
                                        <button class="isup-notif-action isup-notif-action-danger" title="Supprimer" @click.stop="destroy(n)">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="isup-notif-msg">{{ n.message }}</div>
                                <div class="isup-notif-time-text">
                                    {{ relativeDate(n.created_at) }}
                                    <span v-if="n.read_at" class="isup-notif-read-badge"><i class="bi-check-circle-fill"></i> Lue</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <nav v-if="lastPage > 1" class="d-flex justify-content-center align-items-center gap-2 mt-3">
                        <button class="isup-btn-page" :disabled="currentPage <= 1" @click="goToPage(currentPage - 1)">&laquo;</button>
                        <button v-for="page in lastPage" :key="page"
                                class="isup-btn-page" :class="{ 'isup-btn-page-active': page === currentPage }"
                                @click="goToPage(page)">{{ page }}</button>
                        <button class="isup-btn-page" :disabled="currentPage >= lastPage" @click="goToPage(currentPage + 1)">&raquo;</button>
                    </nav>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>

<style scoped>
/* ── Notification-specific styles ── */
.isup-btn-outline { background:#fff;border:1px solid #dce3ee;border-radius:4px;padding:6px 12px;font-size:11px;font-weight:600;color:#555;cursor:pointer;white-space:nowrap; }
.isup-btn-outline:hover { background:#f8fbff;border-color:#FF7900;color:#FF7900; }
.isup-notif-list { border:1px solid #eef2f7;border-radius:4px;overflow:hidden; }
.isup-notif-item { display:flex;align-items:flex-start;gap:12px;padding:12px 14px;border-bottom:1px solid #f0f4f8;cursor:pointer;transition:background .1s; }
.isup-notif-item:last-child { border-bottom:none; }
.isup-notif-item:hover { background:#fafcfd; }
.isup-notif-unread { background:#f0f7ff;border-left:3px solid #FF7900; }
.isup-notif-icon { width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;margin-top:2px; }
.notif-icon-blue { background:#e3f2fd;color:#1565c0; }
.notif-icon-orange { background:#fff3e0;color:#e65100; }
.notif-icon-green { background:#e8f5e9;color:#2e7d32; }
.notif-icon-red { background:#fdecea;color:#c62828; }
.isup-notif-title { font-size:13px;font-weight:700;color:#163A5E; }
.isup-notif-msg { font-size:12px;color:#666;margin-top:2px;line-height:1.4; }
.isup-notif-time-text { font-size:10px;color:#aaa;margin-top:4px; }
.isup-notif-read-badge { color:#2e7d32;margin-left:8px; }
.isup-badge-notif { font-size:9px;font-weight:700;padding:1px 6px;border-radius:3px;text-transform:uppercase; }
.badge-info { background:#e3f2fd;color:#1565c0; }
.badge-success { background:#e8f5e9;color:#2e7d32; }
.badge-warning { background:#fff3e0;color:#e65100; }
.badge-error { background:#fdecea;color:#c62828; }
.isup-notif-action { background:none;border:none;color:#aaa;cursor:pointer;padding:2px;border-radius:3px;line-height:1; }
.isup-notif-action:hover { color:#163A5E;background:#f0f4f8; }
.isup-notif-action-danger:hover { color:#c62828;background:#fdecea; }
.isup-btn-page { background:#f3f4f6;border:1px solid #dce3ee;border-radius:4px;padding:4px 12px;font-size:13px;cursor:pointer;color:#555;min-width:32px;text-align:center; }
.isup-btn-page:hover { background:#e5e7eb; }
.isup-btn-page:disabled { opacity:.4;cursor:not-allowed; }
.isup-btn-page-active { background:#163A5E;color:#fff;border-color:#163A5E; }
.isup-empty-state { text-align:center;padding:40px 20px; }
</style>
