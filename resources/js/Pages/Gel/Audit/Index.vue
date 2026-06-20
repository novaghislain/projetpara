<template>
    <GelLayout page-title="Journal d'Audit">
        <div class="p-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="text-xl fw-bold mb-0">Journal d'Audit</h2>
                <a :href="exportUrl" class="btn btn-outline-secondary btn-sm" v-if="exportUrl">
                    <i class="bi-download me-1"></i> Export Excel
                </a>
            </div>

            <!-- Filtres -->
            <div class="bg-white rounded-lg shadow p-3 mb-3">
                <form class="row g-2 align-items-end" method="GET">
                    <div class="col-md-3">
                        <label class="form-label small">Événement</label>
                        <select name="event" class="form-select form-select-sm" @change="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="create">Création</option>
                            <option value="update">Modification</option>
                            <option value="delete">Suppression</option>
                            <option value="login">Connexion</option>
                            <option value="export">Export</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Utilisateur</label>
                        <input type="text" name="user" class="form-control form-control-sm" placeholder="ID ou email" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Modèle</label>
                        <input type="text" name="model" class="form-control form-control-sm" placeholder="Ex: ErpInvoice" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date début</label>
                        <input type="date" name="from" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date fin</label>
                        <input type="date" name="to" class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">IP</label>
                        <input type="text" name="ip" class="form-control form-control-sm" placeholder="Adresse IP" />
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi-search me-1"></i> Filtrer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Liste -->
            <div class="bg-white rounded-lg shadow">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Événement</th>
                                <th>Utilisateur</th>
                                <th>Modèle</th>
                                <th>Description</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in logs.data" :key="log.id">
                                <td class="text-nowrap small">{{ log.created_at }}</td>
                                <td>
                                    <span :class="'badge ' + eventClass(log.event)">{{ log.event }}</span>
                                </td>
                                <td>{{ log.user?.name || '—' }}</td>
                                <td><code class="small">{{ log.auditable_type?.split('\\').pop() }}</code></td>
                                <td class="small text-truncate" style="max-width:300px;">{{ log.description }}</td>
                                <td><code class="small">{{ log.ip_address }}</code></td>
                            </tr>
                            <tr v-if="!logs.data.length">
                                <td colspan="6" class="text-center text-muted py-4">Aucune entrée d'audit.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-2" v-if="logs.last_page > 1">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item" :class="{ disabled: logs.current_page === 1 }">
                                <a class="page-link" :href="logs.path + '?page=' + (logs.current_page - 1)">‹</a>
                            </li>
                            <li class="page-item" v-for="p in logs.last_page" :key="p" :class="{ active: p === logs.current_page }">
                                <a class="page-link" :href="logs.path + '?page=' + p">{{ p }}</a>
                            </li>
                            <li class="page-item" :class="{ disabled: logs.current_page === logs.last_page }">
                                <a class="page-link" :href="logs.path + '?page=' + (logs.current_page + 1)">›</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['logs'])
const exportUrl = '/administration/audit/export?' + new URLSearchParams(window.location.search).toString()

const eventClass = (e) => ({
    create: 'bg-success',
    update: 'bg-primary',
    delete: 'bg-danger',
    login: 'bg-info text-dark',
    logout: 'bg-secondary',
    export: 'bg-warning text-dark',
}[e] || 'bg-secondary')
</script>
