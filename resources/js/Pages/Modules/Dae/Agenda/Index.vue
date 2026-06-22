<template>
<GelLayout>
    <div class="dae-agenda-index">
        <!-- ═══ LOADING STATE ═══ -->
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement de l'agenda...</p>
        </div>

        <!-- ═══ MAIN CONTENT ═══ -->
        <div v-else class="dae-content">
            <!-- Page Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour au tableau de bord">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>Agenda
                        </h1>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-calendar3 me-1"></i>Gestion des evenements
                            <span class="mx-2">|</span>
                            <span v-if="allEvents.length">{{ allEvents.length }} evenement(s)</span>
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" @click="goToday">
                        <i class="bi bi-calendar-check me-1"></i>Aujourd'hui
                    </button>
                    <button class="btn btn-primary btn-sm" @click="openCreateModal">
                        <i class="bi bi-plus-lg me-1"></i>Nouvel evenement
                    </button>
                </div>
            </div>

            <!-- ═══ CALENDAR CARD ═══ -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <!-- Navigation -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <button class="btn btn-outline-secondary btn-sm me-1" @click="prevMonth" title="Mois precedent">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" @click="nextMonth" title="Mois suivant">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                        <h5 class="mb-0 fw-bold">{{ monthYearLabel }}</h5>
                        <div class="text-muted small">
                            <i class="bi bi-dot me-1"></i>{{ eventCountLabel }}
                        </div>
                    </div>

                    <!-- Month Grid -->
                    <table class="table table-bordered mb-0 dae-calendar-table">
                        <thead>
                            <tr>
                                <th v-for="day in dayHeaders" :key="day" class="text-center text-muted small fw-semibold py-2">
                                    {{ day }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(week, wi) in calendarGrid" :key="wi">
                                <td v-for="(cell, di) in week" :key="di"
                                    class="dae-calendar-cell"
                                    :class="{
                                        'dae-calendar-cell--outside': !cell.isCurrentMonth,
                                        'dae-calendar-cell--today': cell.isToday,
                                        'dae-calendar-cell--selected': selectedDate === cell.dateStr,
                                        'dae-calendar-cell--clickable': cell.isCurrentMonth,
                                    }"
                                    @click="selectDay(cell)"
                                >
                                    <div class="dae-calendar-day-number">{{ cell.day }}</div>
                                    <div v-if="cell.events.length" class="dae-calendar-event-dots">
                                        <span v-for="(evt, ei) in cell.events.slice(0, 3)" :key="ei"
                                            class="dae-calendar-event-dot"
                                            :style="{ backgroundColor: evt.backgroundColor || '#0d6efd' }"
                                            :title="evt.title"
                                        ></span>
                                        <span v-if="cell.events.length > 3" class="dae-calendar-more small text-muted">
                                            +{{ cell.events.length - 3 }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ═══ SELECTED DAY EVENTS ═══ -->
            <div v-if="selectedDate" class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                    <span class="fw-semibold small">
                        <i class="bi bi-calendar-day text-primary me-2"></i>
                        Evenements du {{ formatDateFr(selectedDate) }}
                    </span>
                    <span class="badge bg-primary">{{ selectedDayEvents.length }} evenement(s)</span>
                </div>
                <div class="card-body">
                    <DaeEventList :events="selectedDayEvents" />
                </div>
            </div>
        </div>

        <!-- ═══ CREATE / EDIT EVENT MODAL ═══ -->
        <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true" ref="eventModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle text-primary me-2"></i>
                            Nouvel evenement
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" @click="closeModal"></button>
                    </div>
                    <form @submit.prevent="submitEvent">
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Title -->
                                <div class="col-12">
                                    <label class="form-label small fw-medium">
                                        Titre <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" v-model="form.title" class="form-control"
                                           placeholder="Titre de l'evenement" required />
                                </div>

                                <!-- Type + Client -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium">
                                        Type <span class="text-danger">*</span>
                                    </label>
                                    <select v-model="form.type" class="form-select" required>
                                        <option value="" disabled>Choisir un type</option>
                                        <option value="rdv">Rendez-vous</option>
                                        <option value="reunion">Reunion</option>
                                        <option value="appel">Appel</option>
                                        <option value="echeance">Echeance</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium">
                                        Client <span class="text-danger">*</span>
                                    </label>
                                    <select v-model="form.client_id" class="form-select" required>
                                        <option value="" disabled>Selectionner un client</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">
                                            {{ c.nom || c.raison_sociale || c.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Start + End -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium">
                                        Debut <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" v-model="form.start" class="form-control" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium">Fin</label>
                                    <input type="datetime-local" v-model="form.end" class="form-control" />
                                </div>

                                <!-- All day + Location -->
                                <div class="col-md-6">
                                    <div class="form-check mt-2">
                                        <input type="checkbox" v-model="form.all_day" class="form-check-input" id="allDayCheck"
                                               :true-value="1" :false-value="0" />
                                        <label class="form-check-label small" for="allDayCheck">Toute la journee</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium">Lieu</label>
                                    <input type="text" v-model="form.location" class="form-control" placeholder="Lieu" />
                                </div>

                                <!-- Couleur -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium">Couleur</label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" v-model="form.couleur" class="form-control form-control-color me-2"
                                               style="width:60px;height:38px;padding:2px;" />
                                        <span class="small text-muted">{{ form.couleur || '#0d6efd' }}</span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <label class="form-label small fw-medium">Description</label>
                                    <textarea v-model="form.description" class="form-control" rows="3"
                                              placeholder="Description..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="submitting">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi bi-check-lg me-1"></i>
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</GelLayout>
</template>

<script>
import axios from 'axios';
import DaeEventList from '../../../../Components/Dae/DaeEventList.vue';

const DAY_HEADERS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

const TYPE_ICON_MAP = {
    rdv: 'bi-person',
    reunion: 'bi-people',
    appel: 'bi-telephone',
    echeance: 'bi-alarm',
    autre: 'bi-calendar',
};

export default {
    name: 'DaeAgendaIndex',

    components: {
        DaeEventList,
    },

    data() {
        const now = new Date();
        return {
            loading: true,
            submitting: false,
            // Calendar state
            currentYear: now.getFullYear(),
            currentMonth: now.getMonth(), // 0-indexed
            selectedDate: null,
            // Data
            allEvents: [],
            clients: [],
            // Form
            form: {
                client_id: '',
                title: '',
                type: '',
                start: '',
                end: '',
                all_day: 0,
                location: '',
                couleur: '#0d6efd',
                description: '',
            },
            // Modal reference
            modalInstance: null,
        };
    },

    computed: {
        dayHeaders() {
            return DAY_HEADERS;
        },

        monthYearLabel() {
            const date = new Date(this.currentYear, this.currentMonth, 1);
            return date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
        },

        eventCountLabel() {
            const count = this.allEvents.length;
            if (count === 0) return 'Aucun evenement';
            return count + ' evenement' + (count > 1 ? 's' : '');
        },

        /**
         * Build the 6-week calendar grid (rows of 7 days).
         */
        calendarGrid() {
            const year = this.currentYear;
            const month = this.currentMonth;

            // First day of month (0=Sun, 1=Mon, ... 6=Sat)
            const firstDay = new Date(year, month, 1).getDay();
            // Convert to Monday-based: 0=Mon, 1=Tue, ... 6=Sun
            const startOffset = firstDay === 0 ? 6 : firstDay - 1;

            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();

            const today = new Date();
            const todayStr = this.dateToStr(today.getFullYear(), today.getMonth(), today.getDate());

            // Build day objects for the grid
            const allDays = [];

            // Previous month overflow
            for (let i = startOffset - 1; i >= 0; i--) {
                const day = daysInPrevMonth - i;
                const d = new Date(year, month - 1, day);
                allDays.push(this.makeDayObj(d, false, todayStr));
            }

            // Current month
            for (let d = 1; d <= daysInMonth; d++) {
                const date = new Date(year, month, d);
                allDays.push(this.makeDayObj(date, true, todayStr));
            }

            // Next month overflow (fill to 42 cells = 6 weeks)
            const remaining = 42 - allDays.length;
            for (let d = 1; d <= remaining; d++) {
                const date = new Date(year, month + 1, d);
                allDays.push(this.makeDayObj(date, false, todayStr));
            }

            // Split into weeks of 7
            const weeks = [];
            for (let i = 0; i < allDays.length; i += 7) {
                weeks.push(allDays.slice(i, i + 7));
            }
            return weeks;
        },

        /**
         * Events matching the selected date, mapped for DaeEventList.
         */
        selectedDayEvents() {
            if (!this.selectedDate) return [];
            return this.allEvents
                .filter(evt => {
                    const evtDate = this.dateToStrFromIso(evt.start);
                    return evtDate === this.selectedDate;
                })
                .map(evt => ({
                    id: evt.id,
                    title: evt.title,
                    time: this.formatTime(evt.start),
                    type: evt.extendedProps?.type || 'autre',
                    color: evt.backgroundColor || '#0d6efd',
                }));
        },
    },

    created() {
        this.fetchClients();
        this.fetchEvents();
    },

    mounted() {
        // Bootstrap modal
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const el = this.$refs.eventModal;
            this.modalInstance = new bootstrap.Modal(el, { backdrop: 'static' });
            el.addEventListener('hidden.bs.modal', () => this.closeModal());
        }
    },

    methods: {
        // ─── Data fetching ────────────────────────────────────

        async fetchEvents() {
            try {
                const params = this.getMonthBounds();
                const response = await axios.get('/dae/agenda/calendar', { params });
                this.allEvents = response.data || [];
            } catch (error) {
                console.error('Erreur lors du chargement des evenements:', error);
                this.allEvents = [];
            } finally {
                this.loading = false;
            }
        },

        async fetchClients() {
            try {
                const response = await axios.get('/api/clients');
                this.clients = response.data || [];
            } catch (error) {
                console.error('Erreur lors du chargement des clients:', error);
                this.clients = [];
            }
        },

        getMonthBounds() {
            const start = new Date(this.currentYear, this.currentMonth, 1);
            const end = new Date(this.currentYear, this.currentMonth + 1, 0, 23, 59, 59);
            return {
                start: start.toISOString().slice(0, 10),
                end: end.toISOString().slice(0, 10),
            };
        },

        // ─── Calendar navigation ───────────────────────────────

        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentYear--;
                this.currentMonth = 11;
            } else {
                this.currentMonth--;
            }
            this.fetchEvents();
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentYear++;
                this.currentMonth = 0;
            } else {
                this.currentMonth++;
            }
            this.fetchEvents();
        },

        goToday() {
            const now = new Date();
            this.currentYear = now.getFullYear();
            this.currentMonth = now.getMonth();
            this.selectedDate = this.dateToStr(now.getFullYear(), now.getMonth(), now.getDate());
            this.fetchEvents();
        },

        // ─── Day selection ────────────────────────────────────

        selectDay(cell) {
            if (!cell.isCurrentMonth) {
                // Navigate to clicked month
                const date = new Date(cell.dateStr);
                this.currentYear = date.getFullYear();
                this.currentMonth = date.getMonth();
                this.fetchEvents();
            }
            this.selectedDate = cell.dateStr;
        },

        // ─── Modal ─────────────────────────────────────────────

        openCreateModal() {
            const now = new Date();
            // Pre-fill start with current date-time rounded to nearest hour
            now.setMinutes(0, 0, 0);
            now.setHours(now.getHours() + 1);
            const end = new Date(now);
            end.setHours(end.getHours() + 1);

            this.form = {
                client_id: this.clients.length === 1 ? this.clients[0].id : '',
                title: '',
                type: 'rdv',
                start: this.toDateTimeLocal(now),
                end: this.toDateTimeLocal(end),
                all_day: 0,
                location: '',
                couleur: '#0d6efd',
                description: '',
            };

            if (this.modalInstance) {
                this.modalInstance.show();
            }
        },

        closeModal() {
            if (this.modalInstance) {
                this.modalInstance.hide();
            }
        },

        // ─── Form submission ───────────────────────────────────

        async submitEvent() {
            this.submitting = true;
            try {
                const payload = {
                    client_id: this.form.client_id,
                    title: this.form.title,
                    type: this.form.type,
                    description: this.form.description,
                    start_at: this.form.start,
                    end_at: this.form.end || null,
                    all_day: this.form.all_day,
                    location: this.form.location,
                    couleur: this.form.couleur,
                };
                await axios.post('/dae/agenda', payload);
                this.closeModal();
                await this.fetchEvents();
            } catch (error) {
                console.error('Erreur lors de la creation de l\'evenement:', error);
                const msg = error.response?.data?.message
                    || error.response?.data?.errors
                        ? Object.values(error.response.data.errors).flat().join(', ')
                    : 'Erreur lors de la creation. Verifiez les champs.';
                alert(msg);
            } finally {
                this.submitting = false;
            }
        },

        // ─── Helpers ──────────────────────────────────────────

        makeDayObj(date, isCurrentMonth, todayStr) {
            const y = date.getFullYear();
            const m = date.getMonth();
            const d = date.getDate();
            const dateStr = this.dateToStr(y, m, d);

            const events = this.allEvents.filter(evt => {
                const evtDate = this.dateToStrFromIso(evt.start);
                return evtDate === dateStr;
            });

            return {
                day: d,
                dateStr,
                isCurrentMonth,
                isToday: dateStr === todayStr,
                events,
            };
        },

        dateToStr(year, month, day) {
            const y = year;
            const m = String(month + 1).padStart(2, '0');
            const d = String(day).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },

        dateToStrFromIso(isoStr) {
            if (!isoStr) return '';
            return isoStr.slice(0, 10);
        },

        toDateTimeLocal(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            const h = String(date.getHours()).padStart(2, '0');
            const min = String(date.getMinutes()).padStart(2, '0');
            return `${y}-${m}-${d}T${h}:${min}`;
        },

        formatDateFr(dateStr) {
            if (!dateStr) return '';
            try {
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('fr-FR', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                });
            } catch {
                return dateStr;
            }
        },

        formatTime(isoStr) {
            if (!isoStr) return '--:--';
            try {
                const d = new Date(isoStr);
                return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
            } catch {
                return '--:--';
            }
        },
    },
};
</script>

<style scoped>
.dae-agenda-index {
    padding: 20px;
    min-height: 80vh;
}

/* ── Loading State ── */
.dae-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 120px 20px;
}
.dae-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid rgba(255, 121, 0, 0.1);
    border-top-color: #FF7900;
    border-radius: 50%;
    animation: dae-spin 0.7s linear infinite;
}
@keyframes dae-spin {
    to { transform: rotate(360deg); }
}
.dae-loading-text {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

/* ── Calendar Table ── */
.dae-calendar-table {
    table-layout: fixed;
}
.dae-calendar-table thead th {
    background: #f8f9fa;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #dee2e6;
}

.dae-calendar-cell {
    height: 90px;
    vertical-align: top;
    padding: 6px 8px;
    cursor: default;
    transition: background-color 0.15s ease;
    position: relative;
}
.dae-calendar-cell--clickable {
    cursor: pointer;
}
.dae-calendar-cell--clickable:hover {
    background-color: rgba(255, 121, 0, 0.05);
}
.dae-calendar-cell--outside {
    background-color: #f9f9f9;
    opacity: 0.5;
}
.dae-calendar-cell--today .dae-calendar-day-number {
    background-color: #FF7900;
    color: #fff;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.dae-calendar-cell--selected {
    background-color: rgba(255, 121, 0, 0.1) !important;
    box-shadow: inset 0 0 0 2px #FF7900;
}

.dae-calendar-day-number {
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1;
    margin-bottom: 4px;
}

/* ── Event Dots ── */
.dae-calendar-event-dots {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    align-items: center;
    margin-top: 2px;
}
.dae-calendar-event-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.dae-calendar-more {
    font-size: 0.625rem;
    line-height: 1;
    padding-left: 2px;
}

/* ── Modal ── */
.form-label {
    font-weight: 500;
}
.form-control-color {
    cursor: pointer;
}
</style>
