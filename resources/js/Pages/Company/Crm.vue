<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

const activeTab = ref('dashboard');
const setTab = (tab) => { activeTab.value = tab; };

const stats = ref(null);
const contacts = ref([]);
const deals = ref([]);
const interactions = ref([]);
const loading = ref(false);
const error = ref(null);
const successMsg = ref('');

const showContactModal = ref(false);
const showDealModal = ref(false);
const showInteractionModal = ref(false);
const editingContact = ref(null);
const editingDeal = ref(null);
const isSubmitting = ref(false);

const contactForm = ref({ first_name: '', last_name: '', email: '', phone: '', company: '', position: '', category: 'prospect', notes: '', tags: [] });
const dealForm = ref({ contact_id: '', title: '', description: '', amount: null, stage: 'prospection', status: 'open', probability: 50, expected_close_date: '', notes: '' });
const interactionForm = ref({ contact_id: '', deal_id: '', type: 'call', subject: '', description: '', scheduled_at: '', outcome: '' });
const tagInput = ref('');
const searchQuery = ref('');

const filteredContacts = computed(() => {
    if (!searchQuery.value) return contacts.value;
    const q = searchQuery.value.toLowerCase();
    return contacts.value.filter(c => c.first_name.toLowerCase().includes(q) || c.last_name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q) || (c.company||'').toLowerCase().includes(q) || c.category.toLowerCase().includes(q));
});
const filteredDeals = computed(() => {
    if (!searchQuery.value) return deals.value;
    const q = searchQuery.value.toLowerCase();
    return deals.value.filter(d => d.title.toLowerCase().includes(q) || (d.contact_name||'').toLowerCase().includes(q) || d.stage.toLowerCase().includes(q) || d.status.toLowerCase().includes(q));
});

const formatCurrency = (v) => (v===null||v===undefined||isNaN(v))?'0,00':Number(v).toLocaleString('fr-FR',{minimumFractionDigits:2});
const formatDate = (d) => d?new Date(d).toLocaleDateString('fr-FR'):'';

const categoryLabel = (c) => ({client:'Client',partner:'Partenaire',prospect:'Prospect',lead:'Lead',supplier:'Fournisseur'}[c]||c);
const stageLabel = (s) => ({prospection:'Prospection',qualification:'Qualification',proposition:'Proposition',negociation:'Négociation',finalise:'Finalisé'}[s]||s);
const statusLabel = (s) => ({open:'Ouverte',won:'Gagnée',lost:'Perdue',abandoned:'Abandonnée'}[s]||s);
const typeLabel = (t) => ({call:'Appel',email:'Email',meeting:'Réunion',note:'Note',other:'Autre'}[t]||t);
const typeIcon = (t) => ({call:'bi-telephone',email:'bi-envelope',meeting:'bi-people',note:'bi-sticky',other:'bi-three-dots'}[t]||'bi-three-dots');
const categoryBadge = (c) => ({client:'isup-status-green',partner:'isup-status-cyan',prospect:'isup-status-orange',lead:'isup-status-blue',supplier:'isup-status-grey'}[c]||'isup-status-grey');

function addTag() { const t=tagInput.value.trim(); if(t&&!contactForm.value.tags.includes(t))contactForm.value.tags.push(t); tagInput.value=''; }
function removeTag(i) { contactForm.value.tags.splice(i,1); }

const csrf = document.querySelector('meta[name=csrf-token]')?.content;
const h = () => ({'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf});

async function fetchStats() { try{const r=await fetch('/api/company/crm/stats');if(r.ok)stats.value=await r.json()}catch(e){console.error(e)} }
async function fetchContacts() { try{const r=await fetch('/api/company/crm/contacts');if(r.ok)contacts.value=await r.json()}catch(e){console.error(e)} }
async function fetchDeals() { try{const r=await fetch('/api/company/crm/deals');if(r.ok)deals.value=await r.json()}catch(e){console.error(e)} }
async function fetchInteractions() { try{const r=await fetch('/api/company/crm/interactions');if(r.ok)interactions.value=await r.json()}catch(e){console.error(e)} }

function openCreateContact() { editingContact.value=null;contactForm.value={first_name:'',last_name:'',email:'',phone:'',company:'',position:'',category:'prospect',notes:'',tags:[]};tagInput.value='';showContactModal.value=true; }
function openEditContact(c) { editingContact.value=c.id;contactForm.value={first_name:c.first_name,last_name:c.last_name,email:c.email,phone:c.phone||'',company:c.company||'',position:c.position||'',category:c.category,notes:c.notes||'',tags:c.tags||[]};showContactModal.value=true; }
async function saveContact() { isSubmitting.value=true;error.value=null; try{const u=editingContact.value?`/api/company/crm/contacts/${editingContact.value}`:'/api/company/crm/contacts';const m=editingContact.value?'PUT':'POST';const r=await fetch(u,{method:m,headers:h(),body:JSON.stringify(contactForm.value)});if(!r.ok){const d=await r.json();throw new Error(d.message||Object.values(d.errors||{}).flat().join(', '))}successMsg.value='Contact enregistré';showContactModal.value=false;await fetchContacts();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{isSubmitting.value=false} }
async function deleteContact(id) { if(!confirm('Supprimer ce contact ?'))return; try{const r=await fetch(`/api/company/crm/contacts/${id}`,{method:'DELETE',headers:h()});if(!r.ok)throw new Error('Erreur');successMsg.value='Contact supprimé';await fetchContacts();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){alert(e.message)} }

function openCreateDeal() { editingDeal.value=null;dealForm.value={contact_id:'',title:'',description:'',amount:null,stage:'prospection',status:'open',probability:50,expected_close_date:'',notes:''};showDealModal.value=true; }
function openEditDeal(d) { editingDeal.value=d.id;dealForm.value={contact_id:d.contact_id||'',title:d.title,description:d.description||'',amount:d.amount,stage:d.stage,status:d.status,probability:d.probability,expected_close_date:d.expected_close_date||'',notes:d.notes||''};showDealModal.value=true; }
async function saveDeal() { isSubmitting.value=true;error.value=null; try{const u=editingDeal.value?`/api/company/crm/deals/${editingDeal.value}`:'/api/company/crm/deals';const m=editingDeal.value?'PUT':'POST';const r=await fetch(u,{method:m,headers:h(),body:JSON.stringify(dealForm.value)});if(!r.ok){const d=await r.json();throw new Error(d.message||Object.values(d.errors||{}).flat().join(', '))}successMsg.value='Affaire enregistrée';showDealModal.value=false;await fetchDeals();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{isSubmitting.value=false} }
async function deleteDeal(id) { if(!confirm('Supprimer cette affaire ?'))return; try{const r=await fetch(`/api/company/crm/deals/${id}`,{method:'DELETE',headers:h()});if(!r.ok)throw new Error('Erreur');successMsg.value='Affaire supprimée';await fetchDeals();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){alert(e.message)} }

function openCreateInteraction() { interactionForm.value={contact_id:'',deal_id:'',type:'call',subject:'',description:'',scheduled_at:'',outcome:''};showInteractionModal.value=true; }
async function saveInteraction() { isSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/crm/interactions',{method:'POST',headers:h(),body:JSON.stringify(interactionForm.value)});if(!r.ok){const d=await r.json();throw new Error(d.message||Object.values(d.errors||{}).flat().join(', '))}successMsg.value='Interaction enregistrée';showInteractionModal.value=false;await fetchInteractions();await fetchStats();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{isSubmitting.value=false} }

const pipelineStages = ['prospection','qualification','proposition','negociation','finalise'];
const dealsByStage = computed(() => { const m={};pipelineStages.forEach(s=>{m[s]=[]});filteredDeals.value.forEach(d=>{if(m[d.stage])m[d.stage].push(d)});return m; });
const stageTotal = (stage) => (dealsByStage.value[stage]||[]).reduce((s,d)=>s+Number(d.amount||0),0);

onMounted(async () => { loading.value = true; await Promise.all([fetchStats(),fetchContacts(),fetchDeals(),fetchInteractions()]); loading.value = false; });
</script>

<template>
    <CompanyLayout page-title="CRM">
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888;font-size:14px;">Chargement…</span>
        </div>

        <template v-else>
            <div v-if="successMsg" class="isup-alert-success mb-2">{{ successMsg }}</div>

            <div class="isup-shell">
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo"><i class="bi-people" style="font-size:20px;"></i></div>
                        <div>
                            <div class="isup-portal-company">CRM</div>
                            <div class="isup-portal-sub">Contacts, affaires et interactions</div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="isup-tabs mb-3">
                        <button class="isup-tab" :class="{ active: activeTab === 'dashboard' }" @click="setTab('dashboard')"><i class="bi-speedometer2 me-1"></i> Tableau de bord</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'contacts' }" @click="setTab('contacts')"><i class="bi-people me-1"></i> Contacts</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'deals' }" @click="setTab('deals')"><i class="bi-graph-up-arrow me-1"></i> Affaires</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'interactions' }" @click="setTab('interactions')"><i class="bi-chat-dots me-1"></i> Interactions</button>
                    </div>

                    <!-- Dashboard -->
                    <div v-if="activeTab === 'dashboard' && stats">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-blue"><i class="bi-people"></i></div><div><div class="isup-stat-label">Contacts</div><div class="isup-stat-num">{{ stats.contacts?.total||0 }}</div></div></div></div>
                            <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-green"><i class="bi-graph-up-arrow"></i></div><div><div class="isup-stat-label">Affaires ouvertes</div><div class="isup-stat-num">{{ stats.deals?.open||0 }}</div><small style="color:#2e7d32;">{{ stats.deals?.total||0 }} total</small></div></div></div>
                            <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-orange"><i class="bi-cash-stack"></i></div><div><div class="isup-stat-label">Pipeline</div><div class="isup-stat-num" style="font-size:15px;">{{ formatCurrency(stats.deals?.pipeline) }}</div><small style="color:#e65100;">en cours</small></div></div></div>
                            <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-cyan"><i class="bi-trophy"></i></div><div><div class="isup-stat-label">Conversion</div><div class="isup-stat-num">{{ stats.deals?.conversion||0 }}%</div><small style="color:#00838f;">{{ stats.deals?.won||0 }} gagnées</small></div></div></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><div class="isup-panel"><div class="isup-panel-header"><i class="bi-tags me-2" style="color:#FF7900;"></i>Contacts par catégorie</div><div class="isup-panel-body"><div v-if="Object.keys(stats.contacts?.by_category||{}).length"><div v-for="(count,cat) in stats.contacts.by_category" :key="cat" class="d-flex justify-content-between align-items-center mb-1 isup-misc-row"><span><span class="isup-status" :class="categoryBadge(cat)">{{ categoryLabel(cat) }}</span></span><span class="isup-badge-pill">{{ count }}</span></div></div><p v-else style="font-size:12px;color:#888;">Aucun contact</p></div></div></div>
                            <div class="col-md-6"><div class="isup-panel"><div class="isup-panel-header"><i class="bi-diagram-3 me-2" style="color:#FF7900;"></i>Pipeline par étape</div><div class="isup-panel-body"><div v-if="Object.keys(stats.deals?.by_stage||{}).length"><div v-for="(data,stage) in stats.deals.by_stage" :key="stage" class="d-flex justify-content-between align-items-center mb-1 isup-misc-row"><span><span class="isup-status" :class="stage==='finalise'?'isup-status-green':stage==='negociation'?'isup-status-orange':'isup-status-grey'">{{ stageLabel(stage) }}</span><small style="color:#888;margin-left:4px;">({{ data.count }})</small></span><span style="font-weight:700;color:#163A5E;">{{ formatCurrency(data.amount) }}</span></div></div><p v-else style="font-size:12px;color:#888;">Aucune affaire</p></div></div></div>
                        </div>
                        <div class="isup-panel mt-3"><div class="isup-panel-header"><i class="bi-clock-history me-2" style="color:#FF7900;"></i>Interactions récentes</div><div class="isup-panel-body"><div v-if="stats.recent_interactions&&stats.recent_interactions.length"><div v-for="ri in stats.recent_interactions" :key="ri.id" class="d-flex align-items-center gap-3 mb-2 pb-2" style="border-bottom:1px solid #f0f4f8;"><span class="isup-badge" :class="ri.type==='call'?'isup-badge-light':'isup-badge-light'" style="font-size:11px;"><i :class="typeIcon(ri.type)" class="me-1"></i>{{ typeLabel(ri.type) }}</span><div class="flex-grow-1"><div style="font-size:13px;font-weight:600;color:#163A5E;">{{ ri.subject }}</div><div style="font-size:11px;color:#888;">{{ ri.contact_name||'Sans contact' }}</div></div><small style="color:#aaa;">{{ ri.created_at }}</small></div></div><p v-else style="font-size:12px;color:#888;margin:0;">Aucune interaction récente</p></div></div>
                    </div>

                    <!-- Contacts -->
                    <div v-if="activeTab === 'contacts'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="max-width:300px;"><input type="text" class="isup-input" placeholder="Rechercher..." v-model="searchQuery"></div>
                            <button class="isup-btn-primary" @click="openCreateContact"><i class="bi-plus-lg me-1"></i> Nouveau contact</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-people me-2" style="color:#FF7900;"></i>Liste des contacts</div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead><tr><th>Nom</th><th>Email / Téléphone</th><th>Société</th><th>Catégorie</th><th>Tags</th><th class="text-center">Affaires</th><th class="text-center">Actions</th></tr></thead>
                                        <tbody>
                                            <tr v-for="c in filteredContacts" :key="c.id">
                                                <td style="font-weight:600;color:#163A5E;">{{ c.first_name }} {{ c.last_name }}<div style="font-size:11px;color:#888;">{{ c.position||'-' }}</div></td>
                                                <td style="font-size:12px;">{{ c.email }}<br><small style="color:#888;">{{ c.phone||'-' }}</small></td>
                                                <td style="font-size:12px;">{{ c.company||'-' }}</td>
                                                <td><span class="isup-status" :class="categoryBadge(c.category)">{{ categoryLabel(c.category) }}</span></td>
                                                <td><span v-if="c.tags&&c.tags.length"><span v-for="(tag,ti) in c.tags.slice(0,3)" :key="ti" class="isup-badge isup-badge-light me-1">{{ tag }}</span><span v-if="c.tags.length>3" class="isup-badge isup-badge-light">+{{ c.tags.length-3 }}</span></span><span v-else style="color:#aaa;font-size:11px;">-</span></td>
                                                <td class="text-center"><span class="isup-badge-pill">{{ c.deals_count||0 }}</span></td>
                                                <td class="text-center"><button class="isup-icon-btn" title="Modifier" @click="openEditContact(c)"><i class="bi-pencil"></i></button><button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteContact(c.id)"><i class="bi-trash"></i></button></td>
                                            </tr>
                                            <tr v-if="!filteredContacts.length"><td colspan="7" class="text-center isup-empty-cell">Aucun contact trouvé</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Affaires -->
                    <div v-if="activeTab === 'deals'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="max-width:300px;"><input type="text" class="isup-input" placeholder="Rechercher..." v-model="searchQuery"></div>
                            <button class="isup-btn-primary" @click="openCreateDeal"><i class="bi-plus-lg me-1"></i> Nouvelle affaire</button>
                        </div>
                        <!-- Pipeline Kanban -->
                        <div class="row g-2 mb-4 flex-nowrap overflow-auto" style="min-height:200px;">
                            <div v-for="stage in pipelineStages" :key="stage" class="col" style="min-width:210px;">
                                <div class="isup-kanban-col">
                                    <div class="isup-kanban-header d-flex justify-content-between align-items-center">
                                        <span class="isup-status" :class="stage==='finalise'?'isup-status-green':stage==='negociation'?'isup-status-orange':'isup-status-grey'">{{ stageLabel(stage) }}</span>
                                        <small style="color:#888;">{{ formatCurrency(stageTotal(stage)) }}</small>
                                    </div>
                                    <div class="p-2">
                                        <div v-for="deal in (dealsByStage[stage]||[])" :key="deal.id" class="isup-kanban-card" @click="openEditDeal(deal)">
                                            <div style="font-weight:600;color:#163A5E;font-size:12px;">{{ deal.title }}</div>
                                            <div class="d-flex justify-content-between align-items-center mt-1"><small style="color:#888;">{{ formatCurrency(deal.amount) }}</small><span class="isup-status" :class="deal.status==='won'?'isup-status-green':deal.status==='lost'?'isup-status-red':'isup-status-grey'" style="font-size:9px;">{{ statusLabel(deal.status) }}</span></div>
                                            <div v-if="deal.contact_name" style="font-size:10px;color:#888;margin-top:2px;"><i class="bi-person me-1"></i>{{ deal.contact_name }}</div>
                                            <div class="mt-1"><div class="isup-pbar-sm"><div class="isup-pbar-sm-fill" :style="{width:deal.probability+'%'}"></div></div><small style="color:#888;font-size:10px;">{{ deal.probability }}%</small></div>
                                        </div>
                                        <div v-if="!(dealsByStage[stage]||[]).length" class="text-center py-3" style="color:#aaa;font-size:11px;"><i class="bi-inbox" style="font-size:24px;display:block;margin-bottom:4px;"></i>Aucune</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Table affaires -->
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-graph-up-arrow me-2" style="color:#FF7900;"></i>Toutes les affaires</div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead><tr><th>Titre</th><th>Contact</th><th>Montant</th><th>Étape</th><th>Statut</th><th>Probabilité</th><th>Clôture</th><th class="text-center">Actions</th></tr></thead>
                                        <tbody>
                                            <tr v-for="d in filteredDeals" :key="d.id">
                                                <td style="font-weight:600;color:#163A5E;">{{ d.title }}</td>
                                                <td style="font-size:12px;"><template v-if="d.contact_name">{{ d.contact_name }}<div style="font-size:10px;color:#888;">{{ d.contact_company||'' }}</div></template><span v-else style="color:#aaa;">-</span></td>
                                                <td style="font-weight:700;">{{ formatCurrency(d.amount) }}</td>
                                                <td><span class="isup-status" :class="d.stage==='finalise'?'isup-status-green':d.stage==='negociation'?'isup-status-orange':'isup-status-grey'">{{ stageLabel(d.stage) }}</span></td>
                                                <td><span class="isup-status" :class="d.status==='won'?'isup-status-green':d.status==='lost'?'isup-status-red':'isup-status-grey'">{{ statusLabel(d.status) }}</span></td>
                                                <td><div class="d-flex align-items-center gap-1"><div class="isup-pbar-sm" style="flex:1;max-width:80px;"><div class="isup-pbar-sm-fill" :style="{width:d.probability+'%'}"></div></div><small>{{ d.probability }}%</small></div></td>
                                                <td style="font-size:12px;">{{ formatDate(d.expected_close_date)||'-' }}</td>
                                                <td class="text-center"><button class="isup-icon-btn" title="Modifier" @click="openEditDeal(d)"><i class="bi-pencil"></i></button><button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteDeal(d.id)"><i class="bi-trash"></i></button></td>
                                            </tr>
                                            <tr v-if="!filteredDeals.length"><td colspan="8" class="text-center isup-empty-cell">Aucune affaire trouvée</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interactions -->
                    <div v-if="activeTab === 'interactions'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span style="font-size:12px;color:#888;">{{ interactions.length }} interaction(s)</span>
                            <button class="isup-btn-primary" @click="openCreateInteraction"><i class="bi-plus-lg me-1"></i> Nouvelle interaction</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-chat-dots me-2" style="color:#FF7900;"></i>Timeline</div>
                            <div class="isup-panel-body">
                                <div v-if="interactions.length" class="isup-timeline">
                                    <div v-for="inter in interactions" :key="inter.id" class="isup-timeline-item">
                                        <div class="isup-timeline-dot" :class="inter.type==='call'?'dot-green':inter.type==='email'?'dot-blue':inter.type==='meeting'?'dot-orange':'dot-grey'"><i :class="typeIcon(inter.type)"></i></div>
                                        <div class="isup-timeline-content">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div><span class="isup-badge isup-badge-light me-1">{{ typeLabel(inter.type) }}</span><span style="font-weight:600;color:#163A5E;font-size:13px;">{{ inter.subject }}</span></div>
                                                <small style="color:#aaa;white-space:nowrap;margin-left:8px;">{{ inter.created_at }}</small>
                                            </div>
                                            <p v-if="inter.description" style="font-size:12px;color:#666;margin:4px 0;">{{ inter.description }}</p>
                                            <div style="font-size:11px;color:#888;" class="d-flex gap-3 flex-wrap">
                                                <span v-if="inter.contact_name"><i class="bi-person me-1"></i>{{ inter.contact_name }}</span>
                                                <span v-if="inter.deal_title"><i class="bi-graph-up-arrow me-1"></i>{{ inter.deal_title }}</span>
                                                <span v-if="inter.scheduled_at"><i class="bi-calendar me-1"></i>{{ inter.scheduled_at }}</span>
                                                <span v-if="inter.outcome"><i class="bi-check-circle me-1"></i>{{ inter.outcome }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-center py-4" style="color:#aaa;margin:0;"><i class="bi-chat-dots" style="font-size:32px;display:block;margin-bottom:8px;"></i>Aucune interaction enregistrée</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Contact -->
            <div v-if="showContactModal" class="isup-modal-overlay" @click.self="showContactModal = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header"><span><i class="bi-person-badge me-2"></i>{{ editingContact?'Modifier':'Nouveau' }} contact</span><button class="isup-modal-close" @click="showContactModal=false">&times;</button></div>
                    <form @submit.prevent="saveContact">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-md-6"><label class="isup-label">Prénom *</label><input type="text" class="isup-input" v-model="contactForm.first_name" required></div>
                                <div class="col-md-6"><label class="isup-label">Nom *</label><input type="text" class="isup-input" v-model="contactForm.last_name" required></div>
                                <div class="col-md-6"><label class="isup-label">Email *</label><input type="email" class="isup-input" v-model="contactForm.email" required></div>
                                <div class="col-md-6"><label class="isup-label">Téléphone</label><input type="text" class="isup-input" v-model="contactForm.phone"></div>
                                <div class="col-md-6"><label class="isup-label">Société</label><input type="text" class="isup-input" v-model="contactForm.company"></div>
                                <div class="col-md-6"><label class="isup-label">Fonction</label><input type="text" class="isup-input" v-model="contactForm.position"></div>
                                <div class="col-md-6"><label class="isup-label">Catégorie *</label><select class="isup-select" v-model="contactForm.category" required><option value="client">Client</option><option value="partner">Partenaire</option><option value="prospect">Prospect</option><option value="lead">Lead</option><option value="supplier">Fournisseur</option></select></div>
                                <div class="col-md-6"><label class="isup-label">Tags</label><div class="d-flex gap-1"><input type="text" class="isup-input flex-grow-1" v-model="tagInput" placeholder="Ajouter" @keyup.enter.prevent="addTag"><button type="button" class="isup-btn-grey" @click="addTag" style="padding:7px 10px;font-size:11px;"><i class="bi-plus-lg"></i></button></div><div class="mt-1"><span v-for="(tag,ti) in contactForm.tags" :key="ti" class="isup-badge isup-badge-light me-1 mb-1">{{ tag }}<i class="bi-x ms-1" style="cursor:pointer;" @click="removeTag(ti)"></i></span></div></div>
                                <div class="col-12"><label class="isup-label">Notes</label><textarea class="isup-input" rows="3" v-model="contactForm.notes"></textarea></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showContactModal=false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting"><span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>{{ editingContact?'Enregistrer':'Créer' }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Affaire -->
            <div v-if="showDealModal" class="isup-modal-overlay" @click.self="showDealModal = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header"><span><i class="bi-graph-up-arrow me-2"></i>{{ editingDeal?'Modifier':'Nouvelle' }} affaire</span><button class="isup-modal-close" @click="showDealModal=false">&times;</button></div>
                    <form @submit.prevent="saveDeal">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-md-8"><label class="isup-label">Titre *</label><input type="text" class="isup-input" v-model="dealForm.title" required></div>
                                <div class="col-md-4"><label class="isup-label">Montant (CFA) *</label><input type="number" step="0.01" min="0" class="isup-input" v-model="dealForm.amount" required></div>
                                <div class="col-md-6"><label class="isup-label">Contact</label><select class="isup-select" v-model="dealForm.contact_id"><option value="">Sans contact</option><option v-for="c in contacts" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option></select></div>
                                <div class="col-md-3"><label class="isup-label">Étape *</label><select class="isup-select" v-model="dealForm.stage" required><option value="prospection">Prospection</option><option value="qualification">Qualification</option><option value="proposition">Proposition</option><option value="negociation">Négociation</option><option value="finalise">Finalisé</option></select></div>
                                <div class="col-md-3"><label class="isup-label">Statut *</label><select class="isup-select" v-model="dealForm.status" required><option value="open">Ouverte</option><option value="won">Gagnée</option><option value="lost">Perdue</option><option value="abandoned">Abandonnée</option></select></div>
                                <div class="col-md-4"><label class="isup-label">Probabilité (%)</label><input type="number" min="0" max="100" class="isup-input" v-model="dealForm.probability"></div>
                                <div class="col-md-4"><label class="isup-label">Clôture prévue</label><input type="date" class="isup-input" v-model="dealForm.expected_close_date"></div>
                                <div class="col-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="3" v-model="dealForm.description"></textarea></div>
                                <div class="col-12"><label class="isup-label">Notes</label><textarea class="isup-input" rows="2" v-model="dealForm.notes"></textarea></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showDealModal=false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting"><span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>{{ editingDeal?'Enregistrer':'Créer' }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Interaction -->
            <div v-if="showInteractionModal" class="isup-modal-overlay" @click.self="showInteractionModal = false">
                <div class="isup-modal">
                    <div class="isup-modal-header"><span><i class="bi-chat-plus me-2"></i> Nouvelle interaction</span><button class="isup-modal-close" @click="showInteractionModal=false">&times;</button></div>
                    <form @submit.prevent="saveInteraction">
                        <div class="isup-modal-body">
                            <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                            <div class="row g-2">
                                <div class="col-md-6"><label class="isup-label">Type *</label><select class="isup-select" v-model="interactionForm.type" required><option value="call">Appel</option><option value="email">Email</option><option value="meeting">Réunion</option><option value="note">Note</option><option value="other">Autre</option></select></div>
                                <div class="col-md-6"><label class="isup-label">Sujet *</label><input type="text" class="isup-input" v-model="interactionForm.subject" required></div>
                                <div class="col-md-6"><label class="isup-label">Contact</label><select class="isup-select" v-model="interactionForm.contact_id"><option value="">Sans contact</option><option v-for="c in contacts" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option></select></div>
                                <div class="col-md-6"><label class="isup-label">Affaire liée</label><select class="isup-select" v-model="interactionForm.deal_id"><option value="">Sans affaire</option><option v-for="d in deals" :key="d.id" :value="d.id">{{ d.title }}</option></select></div>
                                <div class="col-md-6"><label class="isup-label">Planifié le</label><input type="datetime-local" class="isup-input" v-model="interactionForm.scheduled_at"></div>
                                <div class="col-md-6"><label class="isup-label">Résultat</label><input type="text" class="isup-input" v-model="interactionForm.outcome"></div>
                                <div class="col-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="3" v-model="interactionForm.description"></textarea></div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button type="button" class="isup-btn-grey" @click="showInteractionModal=false">Annuler</button>
                            <button type="submit" class="isup-btn-primary" :disabled="isSubmitting"><span v-if="isSubmitting" class="isup-spinner-sm me-1"></span>Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>

<style scoped>
/* ── CRM-specific styles ── */
.isup-kanban-col { background:#f8fafc;border:1px solid #eef2f7;border-radius:6px;overflow:hidden; }
.isup-kanban-header { padding:8px 10px;border-bottom:1px solid #eef2f7;background:#fff; }
.isup-kanban-card { background:#fff;border:1px solid #dce3ee;border-radius:4px;padding:10px;margin-bottom:8px;cursor:pointer;transition:box-shadow .12s; }
.isup-kanban-card:hover { box-shadow:0 2px 8px rgba(0,0,0,.08); }
.isup-pbar-sm { height:4px;background:#eef2f7;border-radius:2px;overflow:hidden;display:inline-block;vertical-align:middle; }
.isup-pbar-sm-fill { height:100%;background:#43a047;border-radius:2px;transition:width .3s; }
.isup-timeline { position:relative; }
.isup-timeline::before { content:'';position:absolute;left:18px;top:0;bottom:0;width:2px;background:#eef2f7; }
.isup-timeline-item { display:flex;gap:14px;margin-bottom:20px;position:relative; }
.isup-timeline-dot { width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;position:relative;z-index:1; }
.dot-green { background:#e8f5e9;color:#2e7d32; }
.dot-blue { background:#e3f2fd;color:#1565c0; }
.dot-orange { background:#fff3e0;color:#e65100; }
.dot-grey { background:#f5f5f5;color:#757575; }
.isup-timeline-content { flex-grow:1;background:#f8fafc;border:1px solid #eef2f7;border-radius:6px;padding:10px 14px; }
</style>
