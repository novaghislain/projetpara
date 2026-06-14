<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

// ─── Etat actif des onglets ──────────────────────────────────────────────────
const activeTab = ref('projects');
const setTab = (tab) => { activeTab.value = tab; };

// ─── Donnees ─────────────────────────────────────────────────────────────────
const stats = ref(null);
const projects = ref([]);
const tasks = ref([]);
const loading = ref(false);
const error = ref(null);

// ─── Modales Projet ──────────────────────────────────────────────────────────
const showProjectModal = ref(false);
const editingProject = ref(null);
const isSubmitting = ref(false);

const projectForm = ref({
    name: '', description: '', status: 'planning', priority: 'medium',
    start_date: '', end_date: '', budget: null, progress: 0,
});

// ─── Modales Tache ───────────────────────────────────────────────────────────
const showTaskModal = ref(false);
const editingTask = ref(null);

const taskForm = ref({
    project_id: '', name: '', description: '', assigned_to: '',
    status: 'todo', priority: 'medium', due_date: '',
});

// ─── Recherche et filtres ────────────────────────────────────────────────────
const searchQuery = ref('');
const filterProjectId = ref('');

const filteredProjects = computed(() => {
    if (!searchQuery.value) return projects.value;
    const q = searchQuery.value.toLowerCase();
    return projects.value.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.description && p.description.toLowerCase().includes(q)) ||
        p.status.toLowerCase().includes(q) ||
        p.priority.toLowerCase().includes(q)
    );
});

const filteredTasks = computed(() => {
    let result = tasks.value;
    if (filterProjectId.value) {
        result = result.filter(t => t.project_id === parseInt(filterProjectId.value));
    }
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(t =>
            t.name.toLowerCase().includes(q) ||
            (t.description && t.description.toLowerCase().includes(q)) ||
            (t.assigned_to && t.assigned_to.toLowerCase().includes(q))
        );
    }
    return result;
});

// ─── Helpers ─────────────────────────────────────────────────────────────────
const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('fr-FR');
};

const statusBadge = (status) => {
    const map = {
        planning: 'bg-secondary', in_progress: 'bg-primary', completed: 'bg-success',
        on_hold: 'bg-warning text-dark', cancelled: 'bg-danger',
        todo: 'bg-light text-dark', done: 'bg-success',
        low: 'bg-success', medium: 'bg-warning text-dark', high: 'bg-danger', critical: 'bg-dark text-white',
    };
    return map[status] || 'bg-secondary';
};

const statusLabel = (status) => {
    const map = {
        planning: 'Planification', in_progress: 'En cours', completed: 'Termine',
        on_hold: 'En pause', cancelled: 'Annule',
        todo: 'A faire', done: 'Terminee',
        low: 'Basse', medium: 'Moyenne', high: 'Haute', critical: 'Critique',
    };
    return map[status] || status;
};

const projectStatusOptions = ['planning', 'in_progress', 'completed', 'on_hold', 'cancelled'];
const taskStatusOptions = ['todo', 'in_progress', 'done'];
const priorityOptions = ['low', 'medium', 'high', 'critical'];
const taskPriorityOptions = ['low', 'medium', 'high'];

// ─── API Calls ───────────────────────────────────────────────────────────────
async function fetchStats() {
    try {
        const res = await fetch('/api/company/projects/stats');
        if (!res.ok) throw new Error('Erreur chargement statistiques');
        stats.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchProjects() {
    try {
        const res = await fetch('/api/company/projects');
        if (!res.ok) throw new Error('Erreur chargement projets');
        projects.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

async function fetchTasks() {
    try {
        const res = await fetch('/api/company/projects/tasks');
        if (!res.ok) throw new Error('Erreur chargement taches');
        tasks.value = await res.json();
    } catch (e) {
        console.error(e);
    }
}

// ─── Actions Projets ─────────────────────────────────────────────────────────
function openCreateProject() {
    editingProject.value = null;
    projectForm.value = {
        name: '', description: '', status: 'planning', priority: 'medium',
        start_date: '', end_date: '', budget: null, progress: 0,
    };
    showProjectModal.value = true;
}

function openEditProject(proj) {
    editingProject.value = proj.id;
    projectForm.value = {
        name: proj.name,
        description: proj.description || '',
        status: proj.status,
        priority: proj.priority,
        start_date: proj.start_date || '',
        end_date: proj.end_date || '',
        budget: proj.budget,
        progress: proj.progress,
    };
    showProjectModal.value = true;
}

async function saveProject() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const url = editingProject.value
            ? `/api/company/projects/${editingProject.value}`
            : '/api/company/projects';
        const method = editingProject.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(projectForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showProjectModal.value = false;
        await fetchProjects();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

async function deleteProject(id) {
    if (!confirm('Supprimer ce projet ? Toutes les taches associees seront supprimees.')) return;
    try {
        const res = await fetch(`/api/company/projects/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchProjects();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// ─── Actions Taches ──────────────────────────────────────────────────────────
function openCreateTask() {
    editingTask.value = null;
    taskForm.value = {
        project_id: '', name: '', description: '', assigned_to: '',
        status: 'todo', priority: 'medium', due_date: '',
    };
    showTaskModal.value = true;
}

function openEditTask(task) {
    editingTask.value = task.id;
    taskForm.value = {
        project_id: task.project_id,
        name: task.name,
        description: task.description || '',
        assigned_to: task.assigned_to || '',
        status: task.status,
        priority: task.priority,
        due_date: task.due_date || '',
    };
    showTaskModal.value = true;
}

async function saveTask() {
    isSubmitting.value = true;
    error.value = null;
    try {
        const url = editingTask.value
            ? `/api/company/projects/tasks/${editingTask.value}`
            : '/api/company/projects/tasks';
        const method = editingTask.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify(taskForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }

        showTaskModal.value = false;
        await fetchTasks();
        await fetchProjects();
        await fetchStats();
    } catch (e) {
        error.value = e.message;
    } finally {
        isSubmitting.value = false;
    }
}

async function deleteTask(id) {
    if (!confirm('Supprimer cette tache ?')) return;
    try {
        const res = await fetch(`/api/company/projects/tasks/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchTasks();
        await fetchProjects();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

async function updateTaskStatus(task, newStatus) {
    try {
        const res = await fetch(`/api/company/projects/tasks/${task.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ status: newStatus }),
        });
        if (!res.ok) throw new Error('Erreur mise a jour');
        await fetchTasks();
        await fetchProjects();
        await fetchStats();
    } catch (e) {
        alert(e.message);
    }
}

// ─── Initialisation ──────────────────────────────────────────────────────────
onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchStats(), fetchProjects(), fetchTasks()]);
    loading.value = false;
});
</script>

<template>
    <CompanyLayout page-title="Gestion des projets">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <template v-else>
            <!-- Onglets -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'projects' }" @click="setTab('projects')">
                        <i class="bi-kanban me-1"></i> Projets
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'tasks' }" @click="setTab('tasks')">
                        <i class="bi-list-task me-1"></i> Taches
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === 'stats' }" @click="setTab('stats')">
                        <i class="bi-graph-up me-1"></i> Statistiques
                    </button>
                </li>
            </ul>

            <!-- ══════════════════ PROJETS ══════════════════ -->
            <div v-if="activeTab === 'projects'">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div style="max-width: 350px;">
                        <input type="text" class="form-control form-control-sm" placeholder="Rechercher un projet..."
                               v-model="searchQuery">
                    </div>
                    <button class="btn btn-primary btn-sm" @click="openCreateProject">
                        <i class="bi-plus-lg me-1"></i> Nouveau projet
                    </button>
                </div>

                <!-- Cartes projets -->
                <div class="row g-3">
                    <div v-for="proj in filteredProjects" :key="proj.id" class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title fw-bold mb-0">{{ proj.name }}</h6>
                                    <span class="badge" :class="statusBadge(proj.priority)">{{ statusLabel(proj.priority) }}</span>
                                </div>
                                <p class="text-muted small mb-2" v-if="proj.description">
                                    {{ proj.description.length > 100 ? proj.description.slice(0, 100) + '...' : proj.description }}
                                </p>
                                <div class="mb-2">
                                    <span class="badge me-1" :class="statusBadge(proj.status)">{{ statusLabel(proj.status) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="bi-calendar me-1"></i>
                                        {{ proj.start_date ? formatDate(proj.start_date) : 'N/D' }}
                                        &rarr; {{ proj.end_date ? formatDate(proj.end_date) : 'N/D' }}
                                    </small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">
                                        <i class="bi-list-check me-1"></i>
                                        {{ proj.completed_tasks }}/{{ proj.tasks_count }} taches
                                    </small>
                                    <small class="fw-semibold">{{ proj.progress }}%</small>
                                </div>
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar" :class="{
                                        'bg-success': proj.progress >= 75,
                                        'bg-warning': proj.progress >= 25 && proj.progress < 75,
                                        'bg-danger': proj.progress < 25
                                    }" :style="{ width: proj.progress + '%' }"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted" v-if="proj.budget">
                                        <i class="bi-cash me-1"></i>{{ formatCurrency(proj.budget) }}
                                    </small>
                                    <small class="text-muted" v-else>
                                        <i class="bi-cash me-1"></i>Pas de budget
                                    </small>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Modifier" @click="openEditProject(proj)">
                                            <i class="bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteProject(proj.id)">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="!filteredProjects.length" class="col-12">
                        <div class="text-center text-muted py-5">
                            <i class="bi-kanban fs-1 d-block mb-2"></i>
                            Aucun projet trouve
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ TACHES ══════════════════ -->
            <div v-if="activeTab === 'tasks'">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <div style="max-width: 250px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Rechercher..."
                                   v-model="searchQuery">
                        </div>
                        <div style="min-width: 200px;">
                            <select class="form-select form-select-sm" v-model="filterProjectId">
                                <option value="">Tous les projets</option>
                                <option v-for="proj in projects" :key="proj.id" :value="proj.id">
                                    {{ proj.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm" @click="openCreateTask">
                        <i class="bi-plus-lg me-1"></i> Nouvelle tache
                    </button>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tache</th>
                                    <th>Projet</th>
                                    <th>Assignee</th>
                                    <th>Priorite</th>
                                    <th>Echeance</th>
                                    <th>Statut</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="task in filteredTasks" :key="task.id">
                                    <td class="fw-semibold">
                                        {{ task.name }}
                                        <div class="text-muted small text-truncate" style="max-width: 200px;" v-if="task.description">
                                            {{ task.description }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ task.project_name }}</span>
                                    </td>
                                    <td>
                                        <span v-if="task.assigned_to">
                                            <i class="bi-person me-1"></i>{{ task.assigned_to }}
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="statusBadge(task.priority)">{{ statusLabel(task.priority) }}</span>
                                    </td>
                                    <td>
                                        <span v-if="task.due_date" :class="{ 'text-danger fw-bold': new Date(task.due_date + 'T00:00:00') < new Date() && task.status !== 'done' }">
                                            {{ formatDate(task.due_date) }}
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm dropdown-toggle border-0" :class="statusBadge(task.status)" type="button" data-bs-toggle="dropdown">
                                                {{ statusLabel(task.status) }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li v-for="s in taskStatusOptions" :key="s">
                                                    <a class="dropdown-item small" :class="{ 'fw-bold': task.status === s }" href="#" @click.prevent="updateTaskStatus(task, s)">
                                                        {{ statusLabel(s) }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Modifier" @click="openEditTask(task)">
                                            <i class="bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteTask(task.id)">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!filteredTasks.length">
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi-list-task fs-4 d-block mb-1"></i>
                                        Aucune tache trouvee
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ STATISTIQUES ══════════════════ -->
            <div v-if="activeTab === 'stats' && stats">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi-kanban"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Projets</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.total_projects }}</div>
                                    <small class="text-primary">{{ stats.active_projects }} actifs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi-list-check"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Taches</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.total_tasks }}</div>
                                    <small class="text-success">{{ stats.completed_tasks }} terminees</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi-hourglass-split"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">En attente</div>
                                    <div class="h3 mb-0 fw-bold">{{ stats.pending_tasks }}</div>
                                    <small class="text-warning">{{ stats.overdue_tasks }} en retard</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi-cash-stack"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Budget total</div>
                                    <div class="h3 mb-0 fw-bold">{{ formatCurrency(stats.total_budget) }}</div>
                                    <small class="text-info">Progression {{ stats.avg_progress }}%</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card card-dashboard p-3">
                            <h6 class="fw-bold mb-3"><i class="bi-pie-chart me-2"></i>Par statut</h6>
                            <div v-if="Object.keys(stats.by_status || {}).length">
                                <div v-for="(count, status) in stats.by_status" :key="status" class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <span class="badge me-2" :class="statusBadge(status)">{{ statusLabel(status) }}</span>
                                    </span>
                                    <span class="badge bg-primary rounded-pill">{{ count }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted small mb-0">Aucun projet</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-dashboard p-3">
                            <h6 class="fw-bold mb-3"><i class="bi-flag me-2"></i>Par priorite</h6>
                            <div v-if="Object.keys(stats.by_priority || {}).length">
                                <div v-for="(count, priority) in stats.by_priority" :key="priority" class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <span class="badge me-2" :class="statusBadge(priority)">{{ statusLabel(priority) }}</span>
                                    </span>
                                    <span class="badge bg-secondary rounded-pill">{{ count }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted small mb-0">Aucun projet</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════ MODAL PROJET ══════════════════ -->
            <div class="modal fade" :class="{ show: showProjectModal }" :style="{ display: showProjectModal ? 'block' : 'none' }"
                 tabindex="-1" @click.self="showProjectModal = false">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi-kanban me-1"></i>
                                {{ editingProject ? 'Modifier' : 'Nouveau' }} projet
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="showProjectModal = false"></button>
                        </div>
                        <form @submit.prevent="saveProject">
                            <div class="modal-body">
                                <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="projectForm.name" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Description</label>
                                        <textarea class="form-control form-control-sm" rows="3" v-model="projectForm.description"></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Statut <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="projectForm.status" required>
                                            <option v-for="s in projectStatusOptions" :key="s" :value="s">{{ statusLabel(s) }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Priorite <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="projectForm.priority" required>
                                            <option v-for="p in priorityOptions" :key="p" :value="p">{{ statusLabel(p) }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Progression (%)</label>
                                        <input type="number" min="0" max="100" class="form-control form-control-sm" v-model="projectForm.progress">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Date de debut</label>
                                        <input type="date" class="form-control form-control-sm" v-model="projectForm.start_date">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Date de fin</label>
                                        <input type="date" class="form-control form-control-sm" v-model="projectForm.end_date">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Budget (CFA)</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" v-model="projectForm.budget">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" @click="showProjectModal = false">Annuler</button>
                                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ editingProject ? 'Enregistrer' : 'Creer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div v-if="showProjectModal" class="modal-backdrop fade show"></div>

            <!-- ══════════════════ MODAL TACHE ══════════════════ -->
            <div class="modal fade" :class="{ show: showTaskModal }" :style="{ display: showTaskModal ? 'block' : 'none' }"
                 tabindex="-1" @click.self="showTaskModal = false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi-list-task me-1"></i>
                                {{ editingTask ? 'Modifier' : 'Nouvelle' }} tache
                            </h5>
                            <button type="button" class="btn-close btn-close-white" @click="showTaskModal = false"></button>
                        </div>
                        <form @submit.prevent="saveTask">
                            <div class="modal-body">
                                <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Projet <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="taskForm.project_id" required>
                                            <option value="" disabled>Selectionner un projet</option>
                                            <option v-for="proj in projects" :key="proj.id" :value="proj.id">
                                                {{ proj.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="taskForm.name" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Description</label>
                                        <textarea class="form-control form-control-sm" rows="3" v-model="taskForm.description"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Statut <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="taskForm.status" required>
                                            <option v-for="s in taskStatusOptions" :key="s" :value="s">{{ statusLabel(s) }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Priorite <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="taskForm.priority" required>
                                            <option v-for="p in taskPriorityOptions" :key="p" :value="p">{{ statusLabel(p) }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Assigner a</label>
                                        <input type="text" class="form-control form-control-sm" v-model="taskForm.assigned_to"
                                               placeholder="Nom de la personne">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Echeance</label>
                                        <input type="date" class="form-control form-control-sm" v-model="taskForm.due_date">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" @click="showTaskModal = false">Annuler</button>
                                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ editingTask ? 'Enregistrer' : 'Creer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div v-if="showTaskModal" class="modal-backdrop fade show"></div>
        </template>
    </CompanyLayout>
</template>
