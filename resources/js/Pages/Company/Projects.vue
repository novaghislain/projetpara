<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

const activeTab = ref('projects');
const setTab = (tab) => { activeTab.value = tab; };

const stats = ref(null);
const projects = ref([]);
const tasks = ref([]);
const loading = ref(false);
const error = ref(null);
const successMsg = ref('');

const showProjectModal = ref(false);
const editingProject = ref(null);
const isSubmitting = ref(false);
const projectForm = ref({ name: '', description: '', status: 'planning', priority: 'medium', start_date: '', end_date: '', budget: null, progress: 0 });

const showTaskModal = ref(false);
const editingTask = ref(null);
const taskForm = ref({ project_id: '', name: '', description: '', assigned_to: '', status: 'todo', priority: 'medium', due_date: '' });

const searchQuery = ref('');
const filterProjectId = ref('');

const filteredProjects = computed(() => {
    if (!searchQuery.value) return projects.value;
    const q = searchQuery.value.toLowerCase();
    return projects.value.filter(p => p.name.toLowerCase().includes(q) || (p.description||'').toLowerCase().includes(q) || p.status.toLowerCase().includes(q));
});
const filteredTasks = computed(() => {
    let r = tasks.value;
    if (filterProjectId.value) r = r.filter(t => t.project_id === parseInt(filterProjectId.value));
    if (searchQuery.value) { const q = searchQuery.value.toLowerCase(); r = r.filter(t => t.name.toLowerCase().includes(q) || (t.description||'').toLowerCase().includes(q) || (t.assigned_to||'').toLowerCase().includes(q)); }
    return r;
});

const formatCurrency = (v) => (v===null||v===undefined||isNaN(v))?'0,00':Number(v).toLocaleString('fr-FR',{minimumFractionDigits:2});
const formatDate = (d) => d ? new Date(d+'T00:00:00').toLocaleDateString('fr-FR') : '';

const statusLabel = (s) => ({planning:'Planification',in_progress:'En cours',completed:'Terminé',on_hold:'En pause',cancelled:'Annulé',todo:'À faire',done:'Terminée',low:'Basse',medium:'Moyenne',high:'Haute',critical:'Critique'}[s]||s);
const isStatGreen = (s) => ['completed','done','active','closed','low'].includes(s);
const isStatOrange = (s) => ['in_progress','todo','medium','on_hold'].includes(s);
const isStatRed = (s) => ['cancelled','high','critical'].includes(s);

async function fetchStats() { try { const r=await fetch('/api/company/projects/stats');if(r.ok)stats.value=await r.json(); }catch(e){console.error(e)} }
async function fetchProjects() { try { const r=await fetch('/api/company/projects');if(r.ok)projects.value=await r.json(); }catch(e){console.error(e)} }
async function fetchTasks() { try { const r=await fetch('/api/company/projects/tasks');if(r.ok)tasks.value=await r.json(); }catch(e){console.error(e)} }

const csrf = document.querySelector('meta[name=csrf-token]')?.content;
const h = () => ({'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf});

function openCreateProject() { editingProject.value=null; projectForm.value={name:'',description:'',status:'planning',priority:'medium',start_date:'',end_date:'',budget:null,progress:0}; showProjectModal.value=true; }
function openEditProject(p) { editingProject.value=p.id; projectForm.value={name:p.name,description:p.description||'',status:p.status,priority:p.priority,start_date:p.start_date||'',end_date:p.end_date||'',budget:p.budget,progress:p.progress}; showProjectModal.value=true; }
async function saveProject() { isSubmitting.value=true;error.value=null; try{const u=editingProject.value?`/api/company/projects/${editingProject.value}`:'/api/company/projects';const m=editingProject.value?'PUT':'POST';const r=await fetch(u,{method:m,headers:h(),body:JSON.stringify(projectForm.value)});if(!r.ok){const d=await r.json();throw new Error(d.message||Object.values(d.errors||{}).flat().join(', '))}successMsg.value='Projet enregistré';showProjectModal.value=false;await fetchProjects();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{isSubmitting.value=false}}
async function deleteProject(id) { if(!confirm('Supprimer ce projet ?'))return; try{const r=await fetch(`/api/company/projects/${id}`,{method:'DELETE',headers:h()});if(!r.ok)throw new Error('Erreur');successMsg.value='Projet supprimé';await fetchProjects();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){alert(e.message)}}

function openCreateTask() { editingTask.value=null; taskForm.value={project_id:'',name:'',description:'',assigned_to:'',status:'todo',priority:'medium',due_date:''}; showTaskModal.value=true; }
function openEditTask(t) { editingTask.value=t.id; taskForm.value={project_id:t.project_id,name:t.name,description:t.description||'',assigned_to:t.assigned_to||'',status:t.status,priority:t.priority,due_date:t.due_date||''}; showTaskModal.value=true; }
async function saveTask() { isSubmitting.value=true;error.value=null; try{const u=editingTask.value?`/api/company/projects/tasks/${editingTask.value}`:'/api/company/projects/tasks';const m=editingTask.value?'PUT':'POST';const r=await fetch(u,{method:m,headers:h(),body:JSON.stringify(taskForm.value)});if(!r.ok){const d=await r.json();throw new Error(d.message||Object.values(d.errors||{}).flat().join(', '))}successMsg.value='Tâche enregistrée';showTaskModal.value=false;await fetchTasks();await fetchProjects();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{isSubmitting.value=false}}
async function deleteTask(id) { if(!confirm('Supprimer cette tâche ?'))return; try{const r=await fetch(`/api/company/projects/tasks/${id}`,{method:'DELETE',headers:h()});if(!r.ok)throw new Error('Erreur');successMsg.value='Tâche supprimée';await fetchTasks();await fetchProjects();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){alert(e.message)}}
async function updateTaskStatus(t,s) { try{const r=await fetch(`/api/company/projects/tasks/${t.id}`,{method:'PUT',headers:h(),body:JSON.stringify({status:s})});if(!r.ok)throw new Error('Erreur');await fetchTasks();await fetchProjects();await fetchStats()}catch(e){alert(e.message)}}

onMounted(async () => { loading.value = true; await Promise.all([fetchStats(), fetchProjects(), fetchTasks()]); loading.value = false; });
</script>

<template>
    <CompanyLayout page-title="Gestion des projets">
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888;font-size:14px;">Chargement…</span>
        </div>

        <template v-else>
            <div v-if="successMsg" class="isup-alert-success mb-2">{{ successMsg }}</div>

            <div class="isup-shell">
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo"><i class="bi-kanban" style="font-size:20px;"></i></div>
                        <div>
                            <div class="isup-portal-company">Gestion des projets</div>
                            <div class="isup-portal-sub">Projets et tâches</div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <!-- Tabs -->
                    <div class="isup-tabs mb-3">
                        <button class="isup-tab" :class="{ active: activeTab === 'projects' }" @click="setTab('projects')"><i class="bi-kanban me-1"></i> Projets</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'tasks' }" @click="setTab('tasks')"><i class="bi-list-task me-1"></i> Tâches</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'stats' }" @click="setTab('stats')"><i class="bi-graph-up me-1"></i> Statistiques</button>
                    </div>

                    <!-- Projets -->
                    <div v-if="activeTab === 'projects'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="max-width:300px;"><input type="text" class="isup-input" placeholder="Rechercher..." v-model="searchQuery"></div>
                            <button class="isup-btn-primary" @click="openCreateProject"><i class="bi-plus-lg me-1"></i> Nouveau projet</button>
                        </div>
                        <div class="row g-3">
                            <div v-for="proj in filteredProjects" :key="proj.id" class="col-md-6 col-lg-4">
                                <div class="isup-project-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="isup-project-name">{{ proj.name }}</div>
                                        <span class="isup-status" :class="proj.priority==='critical'?'isup-status-red':proj.priority==='high'?'isup-status-orange':proj.priority==='low'?'isup-status-green':'isup-status-grey'">{{ statusLabel(proj.priority) }}</span>
                                    </div>
                                    <p class="isup-project-desc" v-if="proj.description">{{ proj.description.length>100?proj.description.slice(0,100)+'...':proj.description }}</p>
                                    <div class="mb-1"><span class="isup-status" :class="isStatGreen(proj.status)?'isup-status-green':isStatOrange(proj.status)?'isup-status-orange':'isup-status-red'">{{ statusLabel(proj.status) }}</span></div>
                                    <div class="d-flex justify-content-between" style="font-size:11px;color:#888;">
                                        <span><i class="bi-calendar me-1"></i>{{ proj.start_date?formatDate(proj.start_date):'N/D' }} → {{ proj.end_date?formatDate(proj.end_date):'N/D' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small style="color:#888;"><i class="bi-list-check me-1"></i>{{ proj.completed_tasks }}/{{ proj.tasks_count }} tâches</small>
                                        <small style="font-weight:700;color:#163A5E;">{{ proj.progress }}%</small>
                                    </div>
                                    <div class="isup-progress-bar mt-1">
                                        <div class="isup-progress-fill" :style="{width:proj.progress+'%'}" :class="{'fill-danger':proj.progress<25,'fill-warning':proj.progress>=25&&proj.progress<75,'fill-success':proj.progress>=75}"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span v-if="proj.budget" style="font-size:11px;color:#888;"><i class="bi-cash me-1"></i>{{ formatCurrency(proj.budget) }}</span>
                                        <span v-else style="font-size:11px;color:#888;"><i class="bi-cash me-1"></i>Pas de budget</span>
                                        <div>
                                            <button class="isup-icon-btn" title="Modifier" @click="openEditProject(proj)"><i class="bi-pencil"></i></button>
                                            <button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteProject(proj.id)"><i class="bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="!filteredProjects.length" class="col-12">
                                <div class="text-center py-5" style="color:#aaa;"><i class="bi-kanban" style="font-size:40px;display:block;margin-bottom:10px;"></i>Aucun projet trouvé</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tâches -->
                    <div v-if="activeTab === 'tasks'">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="d-flex gap-2 flex-wrap">
                                <div style="max-width:220px;"><input type="text" class="isup-input" placeholder="Rechercher..." v-model="searchQuery"></div>
                                <select class="isup-select" v-model="filterProjectId" style="width:auto;min-width:180px;">
                                    <option value="">Tous les projets</option>
                                    <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                            <button class="isup-btn-primary" @click="openCreateTask"><i class="bi-plus-lg me-1"></i> Nouvelle tâche</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-list-task me-2" style="color:#FF7900;"></i>Liste des tâches</div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead>
                                            <tr><th>Tâche</th><th>Projet</th><th>Assigné</th><th>Priorité</th><th>Échéance</th><th>Statut</th><th class="text-center">Actions</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="task in filteredTasks" :key="task.id">
                                                <td style="font-weight:600;color:#163A5E;">{{ task.name }}<div v-if="task.description" style="font-size:11px;color:#888;max-width:180px;" class="text-truncate">{{ task.description }}</div></td>
                                                <td><span class="isup-badge isup-badge-light">{{ task.project_name }}</span></td>
                                                <td style="font-size:12px;"><span v-if="task.assigned_to"><i class="bi-person me-1"></i>{{ task.assigned_to }}</span><span v-else style="color:#aaa;">-</span></td>
                                                <td><span class="isup-status" :class="task.priority==='high'?'isup-status-orange':task.priority==='critical'?'isup-status-red':task.priority==='low'?'isup-status-green':'isup-status-grey'">{{ statusLabel(task.priority) }}</span></td>
                                                <td style="font-size:12px;"><span v-if="task.due_date" :class="{'isup-overdue':new Date(task.due_date+'T00:00:00')<new Date()&&task.status!=='done'}">{{ formatDate(task.due_date) }}</span><span v-else style="color:#aaa;">-</span></td>
                                                <td>
                                                    <select class="isup-select isup-select-sm" :value="task.status" @change="updateTaskStatus(task, $event.target.value)" style="width:auto;min-width:100px;font-size:11px;padding:2px 6px;">
                                                        <option value="todo">À faire</option><option value="in_progress">En cours</option><option value="done">Terminée</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button class="isup-icon-btn" title="Modifier" @click="openEditTask(task)"><i class="bi-pencil"></i></button>
                                                    <button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteTask(task.id)"><i class="bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr v-if="!filteredTasks.length"><td colspan="7" class="text-center isup-empty-cell">Aucune tâche trouvée</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div v-if="activeTab === 'stats' && stats">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-blue"><i class="bi-kanban"></i></div>
                                    <div><div class="isup-stat-label">Projets</div><div class="isup-stat-num">{{ stats.total_projects }}</div><small style="color:#1565c0;">{{ stats.active_projects }} actifs</small></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-green"><i class="bi-list-check"></i></div>
                                    <div><div class="isup-stat-label">Tâches</div><div class="isup-stat-num">{{ stats.total_tasks }}</div><small style="color:#2e7d32;">{{ stats.completed_tasks }} terminées</small></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-orange"><i class="bi-hourglass-split"></i></div>
                                    <div><div class="isup-stat-label">En attente</div><div class="isup-stat-num">{{ stats.pending_tasks }}</div><small style="color:#e65100;">{{ stats.overdue_tasks }} en retard</small></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-cyan"><i class="bi-cash-stack"></i></div>
                                    <div><div class="isup-stat-label">Budget total</div><div class="isup-stat-num" style="font-size:15px;">{{ formatCurrency(stats.total_budget) }}</div><small style="color:#00838f;">Progression {{ stats.avg_progress }}%</small></div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header"><i class="bi-pie-chart me-2" style="color:#FF7900;"></i>Par statut</div>
                                    <div class="isup-panel-body">
                                        <div v-if="Object.keys(stats.by_status||{}).length">
                                            <div v-for="(count,st) in stats.by_status" :key="st" class="d-flex justify-content-between align-items-center mb-1 isup-misc-row">
                                                <span><span class="isup-status" :class="isStatGreen(st)?'isup-status-green':isStatOrange(st)?'isup-status-orange':'isup-status-grey'">{{ statusLabel(st) }}</span></span>
                                                <span class="isup-badge-pill">{{ count }}</span>
                                            </div>
                                        </div>
                                        <p v-else style="font-size:12px;color:#888;">Aucun projet</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header"><i class="bi-flag me-2" style="color:#FF7900;"></i>Par priorité</div>
                                    <div class="isup-panel-body">
                                        <div v-if="Object.keys(stats.by_priority||{}).length">
                                            <div v-for="(count,pr) in stats.by_priority" :key="pr" class="d-flex justify-content-between align-items-center mb-1 isup-misc-row">
                                                <span><span class="isup-status" :class="pr==='critical'?'isup-status-red':pr==='high'?'isup-status-orange':pr==='low'?'isup-status-green':'isup-status-grey'">{{ statusLabel(pr) }}</span></span>
                                                <span class="isup-badge-pill">{{ count }}</span>
                                            </div>
                                        </div>
                                        <p v-else style="font-size:12px;color:#888;">Aucun projet</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Projet -->
            <div v-if="showProjectModal" class="isup-modal-overlay" @click.self="showProjectModal = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header">
                        <span><i class="bi-kanban me-2"></i>{{ editingProject ? 'Modifier' : 'Nouveau' }} projet</span>
                        <button class="isup-modal-close" @click="showProjectModal = false">&times;</button>
                    </div>
                    <form @submit.prevent="saveProject">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-12"><label class="isup-label">Nom *</label><input type="text" class="isup-input" v-model="projectForm.name" required></div>
                                <div class="col-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="3" v-model="projectForm.description"></textarea></div>
                                <div class="col-md-4"><label class="isup-label">Statut *</label><select class="isup-select" v-model="projectForm.status" required><option value="planning">Planification</option><option value="in_progress">En cours</option><option value="completed">Terminé</option><option value="on_hold">En pause</option><option value="cancelled">Annulé</option></select></div>
                                <div class="col-md-4"><label class="isup-label">Priorité *</label><select class="isup-select" v-model="projectForm.priority" required><option value="low">Basse</option><option value="medium">Moyenne</option><option value="high">Haute</option><option value="critical">Critique</option></select></div>
                                <div class="col-md-4"><label class="isup-label">Progression (%)</label><input type="number" min="0" max="100" class="isup-input" v-model="projectForm.progress"></div>
                                <div class="col-md-4"><label class="isup-label">Date début</label><input type="date" class="isup-input" v-model="projectForm.start_date"></div>
                                <div class="col-md-4"><label class="isup-label">Date fin</label><input type="date" class="isup-input" v-model="projectForm.end_date"></div>
                                <div class="col-md-4"><label class="isup-label">Budget (CFA)</label><input type="number" step="0.01" class="isup-input" v-model="projectForm.budget"></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showProjectModal = false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>{{ editingProject ? 'Enregistrer' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Tâche -->
            <div v-if="showTaskModal" class="isup-modal-overlay" @click.self="showTaskModal = false">
                <div class="isup-modal">
                    <div class="isup-modal-header">
                        <span><i class="bi-list-task me-2"></i>{{ editingTask ? 'Modifier' : 'Nouvelle' }} tâche</span>
                        <button class="isup-modal-close" @click="showTaskModal = false">&times;</button>
                    </div>
                    <form @submit.prevent="saveTask">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-12"><label class="isup-label">Projet *</label><select class="isup-select" v-model="taskForm.project_id" required><option value="" disabled>Sélectionner</option><option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option></select></div>
                                <div class="col-12"><label class="isup-label">Nom *</label><input type="text" class="isup-input" v-model="taskForm.name" required></div>
                                <div class="col-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="3" v-model="taskForm.description"></textarea></div>
                                <div class="col-md-6"><label class="isup-label">Statut *</label><select class="isup-select" v-model="taskForm.status" required><option value="todo">À faire</option><option value="in_progress">En cours</option><option value="done">Terminée</option></select></div>
                                <div class="col-md-6"><label class="isup-label">Priorité *</label><select class="isup-select" v-model="taskForm.priority" required><option value="low">Basse</option><option value="medium">Moyenne</option><option value="high">Haute</option></select></div>
                                <div class="col-md-6"><label class="isup-label">Assigné à</label><input type="text" class="isup-input" v-model="taskForm.assigned_to"></div>
                                <div class="col-md-6"><label class="isup-label">Échéance</label><input type="date" class="isup-input" v-model="taskForm.due_date"></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showTaskModal = false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting">
                                <span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>{{ editingTask ? 'Enregistrer' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>

<style scoped>
/* ── Projects-specific styles ── */
.isup-select-sm { padding:4px 8px!important; }
.isup-project-card { background:#fff;border:1px solid #dce3ee;border-radius:6px;padding:14px;height:100%;box-shadow:0 1px 4px rgba(0,0,0,.04);transition:box-shadow .15s; }
.isup-project-card:hover { box-shadow:0 3px 12px rgba(0,0,0,.08); }
.isup-project-name { font-family:'Outfit',sans-serif;font-size:14px;font-weight:700;color:#163A5E; }
.isup-project-desc { font-size:12px;color:#666;margin-bottom:8px;line-height:1.4; }
.isup-progress-bar { height:6px;background:#eef2f7;border-radius:3px;overflow:hidden; }
.isup-progress-fill { height:100%;border-radius:3px;transition:width .5s ease; }
.fill-success { background:linear-gradient(90deg,#43a047,#66bb6a); }
.fill-warning { background:linear-gradient(90deg,#f57c00,#ff9800); }
.fill-danger { background:linear-gradient(90deg,#e53935,#ef5350); }
.isup-overdue { color:#c62828!important;font-weight:700; }
</style>
