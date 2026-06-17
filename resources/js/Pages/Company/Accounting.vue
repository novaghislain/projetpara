<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

const activeTab = ref('dashboard');
const loading = ref(false);
const error = ref(null);
const successMsg = ref('');
const showImportCsv = ref(false);

const csrfToken = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '');

const tabs = [
    { key: 'dashboard', label: 'Dashboard', icon: 'bi-speedometer2' },
    { key: 'accounts', label: 'Plan comptable', icon: 'bi-book' },
    { key: 'journals', label: 'Journaux', icon: 'bi-journal-text' },
    { key: 'balance', label: 'Balance', icon: 'bi-table' },
    { key: 'grand-livre', label: 'Grand livre', icon: 'bi-list-columns' },
    { key: 'bilan', label: 'Bilan', icon: 'bi-bar-chart' },
    { key: 'resultat', label: 'Résultat', icon: 'bi-pie-chart' },
    { key: 'tva', label: 'TVA', icon: 'bi-receipt' },
    { key: 'assets', label: 'Immobilisations', icon: 'bi-building' },
    { key: 'fiscal-years', label: 'Exercices', icon: 'bi-calendar3' },
    { key: 'reconciliation', label: 'Réconciliation', icon: 'bi-arrow-left-right' },
];

const formatCurrency = (v) => { if(v===null||v===undefined||isNaN(v))return'0,00';return Number(v).toLocaleString('fr-FR',{minimumFractionDigits:2}); };
const formatDate = (d) => d?new Date(d).toLocaleDateString('fr-FR'):'';
const journalTypeLabel = (t) => ({achat:'Achats',vente:'Ventes',banque:'Banque',caisse:'Caisse',operations_diverses:'Op. diverses',od:'OD',salaire:'Salaires',investissement:'Investissement',paie:'Paie',hav:'Hav',anouveaux:'A Nouveaux'}[t]||t);
const statusLabel = (s) => ({draft:'Brouillon',posted:'Posté',closed:'Clôturé',open:'Ouvert',locked:'Verrouillé',submitted:'Soumis',approved:'Approuvé',paid:'Payé',matched:'Rapproché'}[s]||s);
const statusBadge = (s) => ({draft:'isup-status-grey',posted:'isup-status-green',closed:'isup-status-grey',open:'isup-status-blue',locked:'isup-status-grey',submitted:'isup-status-cyan',approved:'isup-status-green',paid:'isup-status-blue',matched:'isup-status-blue'}[s]||'isup-status-grey');
const accountTypes = [
    {value:'1',label:'Capitaux propres (classe 1)'},{value:'2',label:'Immobilisations (classe 2)'},{value:'3',label:'Stocks (classe 3)'},
    {value:'4',label:'Tiers (classe 4)'},{value:'5',label:'Financier (classe 5)'},{value:'6',label:'Charges (classe 6)'},
    {value:'7',label:'Produits (classe 7)'},{value:'8',label:'Comptes spéciaux (classe 8)'},{value:'9',label:'Comptes analytiques (classe 9)'},
];

// Dashboard
const stats = ref(null);
const loadStats = async () => { try{const r=await fetch('/api/company/accounting/stats');if(r.ok)stats.value=await r.json()}catch(e){console.error(e)} };

// Plan comptable
const accounts = ref([]);
const accountsLoading = ref(false);
const showAccountModal = ref(false);
const editingAccount = ref(null);
const accountForm = ref({code:'',name:'',type:'1',syscohada_class:'1',is_active:true,has_tva:false,tva_rate:18});
const accountSubmitting = ref(false);
const importFile = ref(null);
const importError = ref('');
const importSuccess = ref('');

const loadAccounts = async () => { accountsLoading.value=true; try{const r=await fetch('/api/company/accounting/accounts');if(!r.ok)throw Error('Erreur');accounts.value=await r.json()}catch(e){error.value=e.message}finally{accountsLoading.value=false} };
const openAccountModal = (a=null) => { error.value=null; if(a){editingAccount.value=a;accountForm.value={code:a.code,name:a.name,type:a.type,syscohada_class:a.syscohada_class||a.type,is_active:a.is_active,has_tva:a.has_tva||false,tva_rate:a.tva_rate||18}}else{editingAccount.value=null;accountForm.value={code:'',name:'',type:'1',syscohada_class:'1',is_active:true,has_tva:false,tva_rate:18}};showAccountModal.value=true; };
const submitAccount = async () => { accountSubmitting.value=true;error.value=null;try{const isEdit=!!editingAccount.value;const r=await fetch(isEdit?`/api/company/accounting/accounts/${editingAccount.value.id}`:'/api/company/accounting/accounts',{method:isEdit?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(accountForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;showAccountModal.value=false;await loadAccounts();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{accountSubmitting.value=false} };
const deleteAccount = async (id) => { if(!confirm('Supprimer ce compte ?'))return; try{const r=await fetch(`/api/company/accounting/accounts/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadAccounts();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const importAccounts = async () => { if(!importFile.value){importError.value='Sélectionnez un fichier CSV.';return}importError.value='';importSuccess.value='';const fd=new FormData();fd.append('csv',importFile.value);try{const r=await fetch('/api/company/accounting/accounts/import',{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:fd});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');importSuccess.value=d.message;await loadAccounts()}catch(e){importError.value=e.message} };

// Journaux
const journals = ref([]);
const journalsLoading = ref(false);
const showJournalModal = ref(false);
const showJournalDetailModal = ref(false);
const selectedJournal = ref(null);
const journalSubmitting = ref(false);
const journalForm = ref({journal_type:'operations_diverses',entry_date:new Date().toISOString().split('T')[0],reference:'',description:'',lines:[{account_id:'',label:'',debit:'',credit:''},{account_id:'',label:'',debit:'',credit:''}]});
const journalTotalDebit = computed(() => journalForm.value.lines.reduce((s,l)=>s+(parseFloat(l.debit)||0),0));
const journalTotalCredit = computed(() => journalForm.value.lines.reduce((s,l)=>s+(parseFloat(l.credit)||0),0));
const journalIsBalanced = computed(() => Math.abs(journalTotalDebit.value-journalTotalCredit.value)<0.01);
const addJournalLine = () => journalForm.value.lines.push({account_id:'',label:'',debit:'',credit:''});
const removeJournalLine = (i) => { if(journalForm.value.lines.length>2)journalForm.value.lines.splice(i,1) };

const loadJournals = async () => { journalsLoading.value=true; try{const r=await fetch('/api/company/accounting/journals');if(!r.ok)throw Error('Erreur');journals.value=await r.json()}catch(e){error.value=e.message}finally{journalsLoading.value=false} };
const openJournalModal = () => { error.value=null;journalForm.value={journal_type:'operations_diverses',entry_date:new Date().toISOString().split('T')[0],reference:'',description:'',lines:[{account_id:'',label:'',debit:'',credit:''},{account_id:'',label:'',debit:'',credit:''}]};showJournalModal.value=true; };
const submitJournal = async () => { journalSubmitting.value=true;error.value=null;try{const r=await fetch('/api/company/accounting/journals',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(journalForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;showJournalModal.value=false;await loadJournals();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{journalSubmitting.value=false} };
const loadJournalDetail = async (id) => { try{const r=await fetch(`/api/company/accounting/journals/${id}`);if(!r.ok)throw Error('Erreur');const d=await r.json();selectedJournal.value=d.journal;showJournalDetailModal.value=true}catch(e){error.value=e.message} };
const postJournal = async (id) => { if(!confirm('Poster cette écriture ?'))return; try{const r=await fetch(`/api/company/accounting/journals/${id}/post`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadJournals();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const reverseJournal = async (id) => { if(!confirm('Créer une extourne ?'))return; try{const r=await fetch(`/api/company/accounting/journals/${id}/reverse`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadJournals();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const deleteJournal = async (id) => { if(!confirm('Supprimer ce brouillon ?'))return; try{const r=await fetch(`/api/company/accounting/journals/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadJournals();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };

// Balance
const balanceData = ref(null);
const balanceLoading = ref(false);
const showBalanceByClass = ref(false);
const fiscalYearFilter = ref('');
const loadBalance = async () => { balanceLoading.value=true; try{const p=new URLSearchParams();if(fiscalYearFilter.value)p.append('fiscal_year_id',fiscalYearFilter.value);const r=await fetch('/api/company/accounting/reports/balance'+(p.toString()?'?'+p.toString():''));if(!r.ok)throw Error('Erreur');balanceData.value=await r.json()}catch(e){error.value=e.message}finally{balanceLoading.value=false} };

// Grand livre
const grandLivreLines = ref([]);
const grandLivreTotals = ref(null);
const grandLivreAccounts = ref([]);
const grandLivreFilter = ref({account_id:'',date_from:'',date_to:'',journal_type:''});
const grandLivreLoading = ref(false);
const loadGrandLivre = async () => { grandLivreLoading.value=true; try{const p=new URLSearchParams();if(grandLivreFilter.value.account_id)p.append('account_id',grandLivreFilter.value.account_id);if(grandLivreFilter.value.date_from)p.append('date_from',grandLivreFilter.value.date_from);if(grandLivreFilter.value.date_to)p.append('date_to',grandLivreFilter.value.date_to);if(grandLivreFilter.value.journal_type)p.append('journal_type',grandLivreFilter.value.journal_type);const r=await fetch('/api/company/accounting/reports/grand-livre'+(p.toString()?'?'+p.toString():''));if(!r.ok)throw Error('Erreur');const d=await r.json();grandLivreLines.value=d.lines;grandLivreTotals.value=d.totals;grandLivreAccounts.value=d.accounts}catch(e){error.value=e.message}finally{grandLivreLoading.value=false} };

// Bilan
const bilanData = ref(null);
const bilanLoading = ref(false);
const loadBilan = async () => { bilanLoading.value=true; try{const p=new URLSearchParams();if(fiscalYearFilter.value)p.append('fiscal_year_id',fiscalYearFilter.value);const r=await fetch('/api/company/accounting/reports/bilan'+(p.toString()?'?'+p.toString():''));if(!r.ok)throw Error('Erreur');bilanData.value=await r.json()}catch(e){error.value=e.message}finally{bilanLoading.value=false} };

// Résultat
const resultatData = ref(null);
const resultatLoading = ref(false);
const loadResultat = async () => { resultatLoading.value=true; try{const p=new URLSearchParams();if(fiscalYearFilter.value)p.append('fiscal_year_id',fiscalYearFilter.value);const r=await fetch('/api/company/accounting/reports/resultat'+(p.toString()?'?'+p.toString():''));if(!r.ok)throw Error('Erreur');resultatData.value=await r.json()}catch(e){error.value=e.message}finally{resultatLoading.value=false} };

// TVA
const tvaDeclarations = ref([]);
const tvaLoading = ref(false);
const showTvaModal = ref(false);
const tvaComputed = ref(null);
const tvaPeriod = ref('');
const tvaSubmitting = ref(false);
const tvaForm = ref({period:'',type:'monthly',tva_collected:0,tva_deductible:0,tva_net:0,notes:''});
const loadTvaDeclarations = async () => { tvaLoading.value=true; try{const r=await fetch('/api/company/tva/declarations');if(!r.ok)throw Error('Erreur');tvaDeclarations.value=await r.json()}catch(e){error.value=e.message}finally{tvaLoading.value=false} };
const computeTva = async () => { if(!tvaPeriod.value)return;tvaSubmitting.value=true;try{const r=await fetch('/api/company/tva/compute',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify({period:tvaPeriod.value})});if(!r.ok)throw Error('Erreur');tvaComputed.value=await r.json();tvaForm.value.tva_collected=tvaComputed.value.tva_collected;tvaForm.value.tva_deductible=tvaComputed.value.tva_deductible;tvaForm.value.tva_net=tvaComputed.value.tva_net}catch(e){error.value=e.message}finally{tvaSubmitting.value=false} };
const openTvaModal = () => { error.value=null;const n=new Date();tvaPeriod.value=n.toISOString().slice(0,7);tvaComputed.value=null;tvaForm.value={period:tvaPeriod.value,type:'monthly',tva_collected:0,tva_deductible:0,tva_net:0,notes:''};showTvaModal.value=true; };
const submitTva = async () => { tvaSubmitting.value=true;error.value=null;try{const r=await fetch('/api/company/tva/declarations',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify({...tvaForm.value,period:tvaPeriod.value})});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;showTvaModal.value=false;await loadTvaDeclarations();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{tvaSubmitting.value=false} };
const submitTvaDeclaration = async (id) => { try{const r=await fetch(`/api/company/tva/declarations/${id}/submit`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadTvaDeclarations();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const approveTvaDeclaration = async (id) => { try{const r=await fetch(`/api/company/tva/declarations/${id}/approve`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadTvaDeclarations();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const deleteTvaDeclaration = async (id) => { if(!confirm('Supprimer ?'))return; try{const r=await fetch(`/api/company/tva/declarations/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadTvaDeclarations();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };

// Immobilisations
const assets = ref([]);
const assetsLoading = ref(false);
const showAssetModal = ref(false);
const showAssetDetailModal = ref(false);
const selectedAsset = ref(null);
const assetSubmitting = ref(false);
const assetForm = ref({designation:'',category:'informatique',acquisition_date:'',gross_value:'',residual_value:'0',depreciation_months:'60',depreciation_method:'linear',notes:''});
const loadAssets = async () => { assetsLoading.value=true; try{const r=await fetch('/api/company/fixed-assets');if(!r.ok)throw Error('Erreur');assets.value=await r.json()}catch(e){error.value=e.message}finally{assetsLoading.value=false} };
const openAssetModal = () => { error.value=null;assetForm.value={designation:'',category:'informatique',acquisition_date:new Date().toISOString().split('T')[0],gross_value:'',residual_value:'0',depreciation_months:'60',depreciation_method:'linear',notes:''};showAssetModal.value=true; };
const submitAsset = async () => { assetSubmitting.value=true;error.value=null;try{const r=await fetch('/api/company/fixed-assets',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(assetForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;showAssetModal.value=false;await loadAssets();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{assetSubmitting.value=false} };
const loadAssetDetail = async (id) => { try{const r=await fetch(`/api/company/fixed-assets/${id}`);if(!r.ok)throw Error('Erreur');const d=await r.json();selectedAsset.value=d;showAssetDetailModal.value=true}catch(e){error.value=e.message} };
const generateSchedule = async (id) => { if(!confirm('Générer/régénérer le plan ?'))return; try{const r=await fetch(`/api/company/fixed-assets/${id}/schedule`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value='Plan d\'amortissement généré.';if(selectedAsset.value?.asset?.id===id)await loadAssetDetail(id);setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const deleteAsset = async (id) => { if(!confirm('Supprimer cette immobilisation ?'))return; try{const r=await fetch(`/api/company/fixed-assets/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadAssets();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };

// Exercices fiscaux
const fiscalYears = ref([]);
const fyLoading = ref(false);
const showFyModal = ref(false);
const showFyCloseModal = ref(false);
const selectedFy = ref(null);
const fySubmitting = ref(false);
const fyForm = ref({year:new Date().getFullYear(),date_start:'',date_end:'',notes:''});
const fyCloseForm = ref({check_balance:false,check_tva:false,check_cnss:false,check_reconciliation:false,check_inventory:false,notes:''});
const loadFiscalYears = async () => { fyLoading.value=true; try{const r=await fetch('/api/company/fiscal-years');if(!r.ok)throw Error('Erreur');fiscalYears.value=await r.json()}catch(e){error.value=e.message}finally{fyLoading.value=false} };
const openFyModal = () => { error.value=null;const y=new Date().getFullYear();fyForm.value={year,date_start:`${y}-01-01`,date_end:`${y}-12-31`,notes:''};showFyModal.value=true; };
const submitFy = async () => { fySubmitting.value=true;error.value=null;try{const r=await fetch('/api/company/fiscal-years',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(fyForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;showFyModal.value=false;await loadFiscalYears();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{fySubmitting.value=false} };
const openFyCloseModal = (fy) => { selectedFy.value=fy;fyCloseForm.value={check_balance:false,check_tva:false,check_cnss:false,check_reconciliation:false,check_inventory:false,notes:''};showFyCloseModal.value=true; };
const submitFyClose = async () => { fySubmitting.value=true;error.value=null;try{const r=await fetch(`/api/company/fiscal-years/${selectedFy.value.id}/close`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(fyCloseForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;showFyCloseModal.value=false;await loadFiscalYears();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{fySubmitting.value=false} };
const reopenFy = async (id) => { if(!confirm('Réouvrir ?'))return; try{const r=await fetch(`/api/company/fiscal-years/${id}/reopen`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadFiscalYears();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const lockFy = async (id) => { if(!confirm('Verrouiller ?'))return; try{const r=await fetch(`/api/company/fiscal-years/${id}/lock`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadFiscalYears();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };

// Réconciliation
const reconciliations = ref([]);
const recoLoading = ref(false);
const showRecoModal = ref(false);
const recoSubmitting = ref(false);
const recoForm = ref({bank_account:'',bank_name:'',period:'',statement_date:'',balance_per_statement:0,balance_per_books:0});
const loadReconciliations = async () => { recoLoading.value=true; try{const r=await fetch('/api/company/bank-reconciliations');if(!r.ok)throw Error('Erreur');reconciliations.value=await r.json()}catch(e){error.value=e.message}finally{recoLoading.value=false} };
const openRecoModal = () => { error.value=null;const n=new Date();recoForm.value={bank_account:'',bank_name:'',period:n.toISOString().slice(0,7),statement_date:n.toISOString().split('T')[0],balance_per_statement:0,balance_per_books:0};showRecoModal.value=true; };
const submitReco = async () => { recoSubmitting.value=true;error.value=null;try{const r=await fetch('/api/company/bank-reconciliations',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(recoForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;showRecoModal.value=false;await loadReconciliations();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message}finally{recoSubmitting.value=false} };
const matchReco = async (id) => { if(!confirm('Marquer comme rapprochée ?'))return; try{const r=await fetch(`/api/company/bank-reconciliations/${id}/match`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadReconciliations();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const approveReco = async (id) => { try{const r=await fetch(`/api/company/bank-reconciliations/${id}/approve`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadReconciliations();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };
const deleteReco = async (id) => { if(!confirm('Supprimer ?'))return; try{const r=await fetch(`/api/company/bank-reconciliations/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');successMsg.value=d.message;await loadReconciliations();setTimeout(()=>successMsg.value='',3000)}catch(e){error.value=e.message} };

const onTabChange = (tab) => {
    activeTab.value=tab;error.value=null;
    if(tab==='dashboard')loadStats();
    if(tab==='accounts'&&!accounts.value.length)loadAccounts();
    if(tab==='journals'&&!journals.value.length)loadJournals();
    if(tab==='balance')loadBalance();
    if(tab==='grand-livre'){if(!grandLivreAccounts.value.length)loadGrandLivre()}
    if(tab==='bilan')loadBilan();
    if(tab==='resultat')loadResultat();
    if(tab==='tva'&&!tvaDeclarations.value.length)loadTvaDeclarations();
    if(tab==='assets'&&!assets.value.length)loadAssets();
    if(tab==='fiscal-years'&&!fiscalYears.value.length)loadFiscalYears();
    if(tab==='reconciliation'&&!reconciliations.value.length)loadReconciliations();
};

onMounted(() => { loadStats();loadAccounts();loadJournals(); });
</script>

<template>
    <CompanyLayout page-title="Comptabilité">
        <div v-if="successMsg" class="isup-alert-success mb-2">{{ successMsg }}</div>
        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>

        <div class="isup-shell">
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo"><i class="bi-calculator" style="font-size:20px;"></i></div>
                    <div>
                        <div class="isup-portal-company">Comptabilité</div>
                        <div class="isup-portal-sub">Plan comptable, journaux, états financiers SYSCOHADA</div>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <div class="isup-tabs mb-3" style="flex-wrap:wrap;">
                    <button v-for="tab in tabs" :key="tab.key" class="isup-tab" :class="{ active: activeTab===tab.key }" @click="onTabChange(tab.key)"><i :class="tab.icon+' me-1'"></i>{{ tab.label }}</button>
                </div>

                <!-- ==================== DASHBOARD ==================== -->
                <div v-if="activeTab==='dashboard'">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-blue"><i class="bi-book"></i></div><div><div class="isup-stat-label">Comptes</div><div class="isup-stat-num">{{ stats?.total_accounts||0 }}</div><small style="color:#1565c0;font-size:10px;">Actifs: {{ stats?.active_accounts||0 }}</small></div></div></div>
                        <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-green"><i class="bi-journal-text"></i></div><div><div class="isup-stat-label">Écritures</div><div class="isup-stat-num">{{ stats?.total_journals||0 }}</div><small style="color:#2e7d32;font-size:10px;">Postés: {{ stats?.posted_journals||0 }}</small></div></div></div>
                        <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-red"><i class="bi-arrow-up-circle"></i></div><div><div class="isup-stat-label">Total débit</div><div class="isup-stat-num" style="font-size:14px;">{{ formatCurrency(stats?.total_debit) }}</div></div></div></div>
                        <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-cyan"><i class="bi-arrow-down-circle"></i></div><div><div class="isup-stat-label">Total crédit</div><div class="isup-stat-num" style="font-size:14px;">{{ formatCurrency(stats?.total_credit) }}</div></div></div></div>
                    </div>
                    <div class="isup-panel"><div class="isup-panel-header"><i class="bi-pie-chart me-2" style="color:#FF7900;"></i>Répartition par type de journal</div><div class="isup-panel-body p-0"><div v-if="!stats?.by_type" class="text-center py-4 isup-empty-cell">Aucune donnée</div><div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th>Type</th><th class="text-end">Nombre</th><th class="text-end">Débit</th><th class="text-end">Crédit</th></tr></thead><tbody><tr v-for="(v,k) in stats.by_type" :key="k"><td style="font-weight:600;">{{ journalTypeLabel(k) }}</td><td class="text-end">{{ v.count }}</td><td class="text-end">{{ formatCurrency(v.debit) }}</td><td class="text-end">{{ formatCurrency(v.credit) }}</td></tr></tbody></table></div></div></div>
                </div>

                <!-- ==================== PLAN COMPTABLE ==================== -->
                <div v-if="activeTab==='accounts'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center"><span><i class="bi-book me-2" style="color:#FF7900;"></i>Plan comptable SYSCOHADA</span><div class="d-flex gap-2"><button class="isup-btn-grey" @click="showImportCsv=!showImportCsv"><i class="bi-upload me-1"></i>Import CSV</button><button class="isup-btn-primary" @click="openAccountModal()"><i class="bi-plus-lg me-1"></i>Nouveau compte</button></div></div>
                        <div v-if="showImportCsv" class="isup-panel-body" style="border-bottom:1px solid #f0f4f8;"><p class="small mb-2" style="color:#888;">Fichier CSV avec colonnes: code, name, type</p><div class="d-flex gap-2"><input type="file" class="isup-input" style="max-width:300px;" accept=".csv" @change="e=>importFile=e.target.files[0]"><button class="isup-btn-primary" @click="importAccounts"><i class="bi-upload me-1"></i>Importer</button></div><div v-if="importError" style="color:#c62828;font-size:11px;margin-top:4px;">{{ importError }}</div><div v-if="importSuccess" style="color:#2e7d32;font-size:11px;margin-top:4px;">{{ importSuccess }}</div></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="accountsLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!accounts.length" class="text-center py-5 isup-empty-cell"><i class="bi-book" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucun compte comptable.</p><button class="isup-btn-primary" @click="openAccountModal()">Créer un compte</button></div>
                            <div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Code</th><th>Libellé</th><th>Classe</th><th>TVA</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end">Solde</th><th class="text-end pe-4">Actions</th></tr></thead><tbody><tr v-for="acc in accounts" :key="acc.id"><td class="ps-4 fw-semibold">{{ acc.code }}</td><td>{{ acc.name }}</td><td><span class="isup-badge isup-badge-light">{{ acc.syscohada_class||acc.type }}</span></td><td><span v-if="acc.has_tva" class="isup-status isup-status-cyan">{{ acc.tva_rate }}%</span><span v-else style="color:#aaa;font-size:11px;">-</span></td><td class="text-end">{{ formatCurrency(acc.debit) }}</td><td class="text-end">{{ formatCurrency(acc.credit) }}</td><td class="text-end fw-semibold" :style="{color: acc.balance<0?'#c62828':'#2e7d32'}">{{ formatCurrency(acc.balance) }}</td><td class="text-end pe-4"><button class="isup-icon-btn me-1" title="Modifier" @click="openAccountModal(acc)"><i class="bi-pencil"></i></button><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteAccount(acc.id)"><i class="bi-trash"></i></button></td></tr></tbody></table></div>
                        </div>
                    </div>
                </div>

                <!-- ==================== JOURNAUX ==================== -->
                <div v-if="activeTab==='journals'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center"><span><i class="bi-journal-text me-2" style="color:#FF7900;"></i>Journaux comptables</span><button class="isup-btn-primary" @click="openJournalModal()"><i class="bi-plus-lg me-1"></i>Nouvelle écriture</button></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="journalsLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!journals.length" class="text-center py-5 isup-empty-cell"><i class="bi-journal-text" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucune écriture.</p><button class="isup-btn-primary" @click="openJournalModal()">Créer une écriture</button></div>
                            <div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Date</th><th>Réf.</th><th>N° Pièce</th><th>Type</th><th>Description</th><th>Statut</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end pe-4">Actions</th></tr></thead><tbody><tr v-for="j in journals" :key="j.id"><td class="ps-4">{{ formatDate(j.entry_date) }}</td><td class="fw-semibold">{{ j.reference }}</td><td><small style="color:#888;">{{ j.numero_piece||'-' }}</small></td><td><span class="isup-badge isup-badge-light">{{ journalTypeLabel(j.journal_type) }}</span></td><td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ j.description||'-' }}</td><td><span class="isup-status" :class="statusBadge(j.status)">{{ statusLabel(j.status) }}</span><span v-if="j.is_reversal" class="isup-status isup-status-grey ms-1">Extourne</span></td><td class="text-end">{{ formatCurrency(j.debit_total) }}</td><td class="text-end">{{ formatCurrency(j.credit_total) }}</td><td class="text-end pe-4"><button class="isup-icon-btn me-1" title="Détail" @click="loadJournalDetail(j.id)"><i class="bi-eye"></i></button><button v-if="j.status==='draft'" class="isup-icon-btn me-1" style="color:#2e7d32;" title="Poster" @click="postJournal(j.id)"><i class="bi-check2-circle"></i></button><button v-if="j.status==='posted'&&!j.is_reversal" class="isup-icon-btn me-1" style="color:#e65100;" title="Extourner" @click="reverseJournal(j.id)"><i class="bi-arrow-return-left"></i></button><button v-if="j.status==='draft'" class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteJournal(j.id)"><i class="bi-trash"></i></button></td></tr></tbody></table></div>
                        </div>
                    </div>
                </div>

                <!-- ==================== BALANCE ==================== -->
                <div v-if="activeTab==='balance'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center"><span><i class="bi-table me-2" style="color:#FF7900;"></i>Balance des comptes</span><div class="d-flex gap-2 align-items-center"><select class="isup-select" style="width:auto;min-width:160px;" v-model="fiscalYearFilter"><option value="">Tous les exercices</option><option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }}</option></select><button class="isup-btn-primary" @click="loadBalance"><i class="bi-search me-1"></i>Actualiser</button><button class="isup-btn-grey" @click="showBalanceByClass=!showBalanceByClass"><i class="bi-layers me-1"></i>{{ showBalanceByClass?'Détail':'Par classe' }}</button></div></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="balanceLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!balanceData" class="text-center py-5 isup-empty-cell"><i class="bi-table" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucune donnée.</p></div>
                            <div v-else-if="showBalanceByClass&&balanceData.by_class" class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Classe</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end pe-4">Solde</th></tr></thead><tbody><tr v-for="cls in balanceData.by_class" :key="cls.class"><td class="ps-4 fw-bold">{{ cls.class }}</td><td>{{ cls.label }}</td><td class="text-end">{{ formatCurrency(cls.debit) }}</td><td class="text-end">{{ formatCurrency(cls.credit) }}</td><td class="text-end pe-4 fw-semibold">{{ formatCurrency(cls.solde) }}</td></tr></tbody></table></div>
                            <div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end pe-4">Solde</th></tr></thead><tbody><tr v-for="acc in balanceData.accounts" :key="acc.code"><td class="ps-4 fw-semibold">{{ acc.code }}</td><td>{{ acc.name }}</td><td class="text-end">{{ formatCurrency(acc.debit) }}</td><td class="text-end">{{ formatCurrency(acc.credit) }}</td><td class="text-end pe-4 fw-semibold" :style="{color:acc.solde<0?'#c62828':'#2e7d32'}">{{ formatCurrency(acc.solde) }}</td></tr></tbody><tfoot style="background:#EEF3F9;font-weight:700;"><tr><td colspan="2" class="ps-4">Totaux</td><td class="text-end">{{ formatCurrency(balanceData.totals.total_debit) }}</td><td class="text-end">{{ formatCurrency(balanceData.totals.total_credit) }}</td><td class="text-end pe-4">{{ formatCurrency(balanceData.totals.total_debit-balanceData.totals.total_credit) }}</td></tr></tfoot></table></div>
                        </div>
                    </div>
                </div>

                <!-- ==================== GRAND LIVRE ==================== -->
                <div v-if="activeTab==='grand-livre'">
                    <div class="isup-panel">
                        <div class="isup-panel-header"><i class="bi-list-columns me-2" style="color:#FF7900;"></i>Grand livre</div>
                        <div class="isup-panel-body">
                            <div class="row g-2 mb-4">
                                <div class="col-md-3"><label class="isup-label">Compte</label><select class="isup-select" v-model="grandLivreFilter.account_id"><option value="">Tous les comptes</option><option v-for="acc in grandLivreAccounts" :key="acc.id" :value="acc.id">{{ acc.code }} - {{ acc.name }}</option></select></div>
                                <div class="col-md-3"><label class="isup-label">Date début</label><input type="date" class="isup-input" v-model="grandLivreFilter.date_from"></div>
                                <div class="col-md-3"><label class="isup-label">Date fin</label><input type="date" class="isup-input" v-model="grandLivreFilter.date_to"></div>
                                <div class="col-md-3 d-flex align-items-end"><button class="isup-btn-primary w-100" @click="loadGrandLivre"><i class="bi-search me-1"></i>Filtrer</button></div>
                            </div>
                            <div v-if="grandLivreLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!grandLivreLines.length" class="text-center py-5 isup-empty-cell"><i class="bi-list-columns" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucune écriture trouvée.</p></div>
                            <div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th>Date</th><th>Réf.</th><th>Compte</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end">Solde cumulé</th></tr></thead><tbody><tr v-for="l in grandLivreLines" :key="l.id"><td>{{ formatDate(l.date) }}</td><td class="fw-semibold">{{ l.reference }}</td><td>{{ l.account_code }} - {{ l.account_name }}</td><td>{{ l.label }}</td><td class="text-end">{{ l.debit?formatCurrency(l.debit):'-' }}</td><td class="text-end">{{ l.credit?formatCurrency(l.credit):'-' }}</td><td class="text-end fw-semibold">{{ formatCurrency(l.solde_cumule) }}</td></tr></tbody><tfoot style="background:#EEF3F9;font-weight:700;"><tr><td colspan="4" class="ps-4">Totaux</td><td class="text-end">{{ formatCurrency(grandLivreTotals.total_debit) }}</td><td class="text-end">{{ formatCurrency(grandLivreTotals.total_credit) }}</td><td></td></tr></tfoot></table></div>
                        </div>
                    </div>
                </div>

                <!-- ==================== BILAN ==================== -->
                <div v-if="activeTab==='bilan'">
                    <div class="d-flex justify-content-end gap-2 mb-3"><select class="isup-select" style="width:auto;min-width:160px;" v-model="fiscalYearFilter"><option value="">Tous les exercices</option><option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }}</option></select><button class="isup-btn-primary" @click="loadBilan"><i class="bi-search me-1"></i>Actualiser</button></div>
                    <div v-if="bilanLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                    <div v-else-if="!bilanData" class="text-center py-5 isup-empty-cell"><i class="bi-bar-chart" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucune donnée pour le bilan.</p></div>
                    <div v-else class="row g-4">
                        <div class="col-md-6"><div class="isup-panel"><div class="isup-panel-header" style="color:#1565c0;">Actif</div><div class="isup-panel-body p-0"><div class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end pe-4">Montant</th></tr></thead><tbody><tr v-for="acc in bilanData.actif.accounts" :key="acc.code"><td class="ps-4 fw-semibold">{{ acc.code }}</td><td>{{ acc.name }}</td><td class="text-end pe-4">{{ formatCurrency(acc.solde) }}</td></tr></tbody><tfoot style="background:#EEF3F9;font-weight:700;"><tr><td colspan="2" class="ps-4">Total Actif</td><td class="text-end pe-4">{{ formatCurrency(bilanData.actif.total) }}</td></tr></tfoot></table></div></div></div></div>
                        <div class="col-md-6"><div class="isup-panel"><div class="isup-panel-header" style="color:#2e7d32;">Passif</div><div class="isup-panel-body p-0"><div class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end pe-4">Montant</th></tr></thead><tbody><tr v-for="acc in bilanData.passif.accounts" :key="acc.code"><td class="ps-4 fw-semibold">{{ acc.code }}</td><td>{{ acc.name }}</td><td class="text-end pe-4">{{ formatCurrency(acc.solde) }}</td></tr></tbody><tfoot style="background:#EEF3F9;font-weight:700;"><tr><td colspan="2" class="ps-4">Total Passif</td><td class="text-end pe-4">{{ formatCurrency(bilanData.passif.total) }}</td></tr></tfoot></table></div></div></div></div>
                    </div>
                </div>

                <!-- ==================== RÉSULTAT ==================== -->
                <div v-if="activeTab==='resultat'">
                    <div class="d-flex justify-content-end gap-2 mb-3"><select class="isup-select" style="width:auto;min-width:160px;" v-model="fiscalYearFilter"><option value="">Tous les exercices</option><option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }}</option></select><button class="isup-btn-primary" @click="loadResultat"><i class="bi-search me-1"></i>Actualiser</button></div>
                    <div v-if="resultatLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                    <div v-else-if="!resultatData" class="text-center py-5 isup-empty-cell"><i class="bi-pie-chart" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucune donnée.</p></div>
                    <div v-else>
                        <div class="row g-4">
                            <div class="col-md-6"><div class="isup-panel"><div class="isup-panel-header" style="color:#c62828;">Charges (classe 6)</div><div class="isup-panel-body p-0"><div class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end pe-4">Montant</th></tr></thead><tbody><tr v-for="acc in resultatData.charges.accounts" :key="acc.code"><td class="ps-4 fw-semibold">{{ acc.code }}</td><td>{{ acc.name }}</td><td class="text-end pe-4">{{ formatCurrency(acc.solde) }}</td></tr></tbody><tfoot style="background:#EEF3F9;font-weight:700;"><tr><td colspan="2" class="ps-4">Total charges</td><td class="text-end pe-4">{{ formatCurrency(resultatData.charges.total) }}</td></tr></tfoot></table></div></div></div></div>
                            <div class="col-md-6"><div class="isup-panel"><div class="isup-panel-header" style="color:#2e7d32;">Produits (classe 7)</div><div class="isup-panel-body p-0"><div class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end pe-4">Montant</th></tr></thead><tbody><tr v-for="acc in resultatData.produits.accounts" :key="acc.code"><td class="ps-4 fw-semibold">{{ acc.code }}</td><td>{{ acc.name }}</td><td class="text-end pe-4">{{ formatCurrency(acc.solde) }}</td></tr></tbody><tfoot style="background:#EEF3F9;font-weight:700;"><tr><td colspan="2" class="ps-4">Total produits</td><td class="text-end pe-4">{{ formatCurrency(resultatData.produits.total) }}</td></tr></tfoot></table></div></div></div></div>
                        </div>
                        <div class="isup-panel mt-4"><div class="isup-panel-body text-center"><div style="font-weight:700;font-size:14px;color:#163A5E;">Résultat net</div><div style="font-size:22px;font-weight:800;" :style="{color:resultatData.resultat>=0?'#2e7d32':'#c62828'}">{{ formatCurrency(resultatData.resultat) }} F</div><small style="color:#888;">{{ resultatData.resultat>=0?'Bénéfice':'Perte' }}</small></div></div>
                    </div>
                </div>

                <!-- ==================== TVA ==================== -->
                <div v-if="activeTab==='tva'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center"><span><i class="bi-receipt me-2" style="color:#FF7900;"></i>Déclarations TVA</span><button class="isup-btn-primary" @click="openTvaModal()"><i class="bi-plus-lg me-1"></i>Nouvelle déclaration</button></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="tvaLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!tvaDeclarations.length" class="text-center py-5 isup-empty-cell"><i class="bi-receipt" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucune déclaration TVA.</p><button class="isup-btn-primary" @click="openTvaModal()">Créer une déclaration</button></div>
                            <div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Période</th><th>Type</th><th class="text-end">Collectée</th><th class="text-end">Déductible</th><th class="text-end">Net à payer</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead><tbody><tr v-for="d in tvaDeclarations" :key="d.id"><td class="ps-4 fw-semibold">{{ d.period }}</td><td><span class="isup-badge isup-badge-light">{{ d.type }}</span></td><td class="text-end">{{ formatCurrency(d.tva_collected) }}</td><td class="text-end">{{ formatCurrency(d.tva_deductible) }}</td><td class="text-end fw-bold" :style="{color:d.tva_net>0?'#c62828':'#2e7d32'}">{{ formatCurrency(d.tva_net) }}</td><td><span class="isup-status" :class="statusBadge(d.status)">{{ statusLabel(d.status) }}</span></td><td class="text-end pe-4"><button v-if="d.status==='draft'" class="isup-icon-btn me-1" style="color:#2e7d32;" title="Soumettre" @click="submitTvaDeclaration(d.id)"><i class="bi-send"></i></button><button v-if="d.status==='submitted'" class="isup-icon-btn me-1" style="color:#1565c0;" title="Approuver" @click="approveTvaDeclaration(d.id)"><i class="bi-check2-circle"></i></button><button v-if="d.status==='draft'" class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteTvaDeclaration(d.id)"><i class="bi-trash"></i></button></td></tr></tbody></table></div>
                        </div>
                    </div>
                </div>

                <!-- ==================== IMMOBILISATIONS ==================== -->
                <div v-if="activeTab==='assets'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center"><span><i class="bi-building me-2" style="color:#FF7900;"></i>Immobilisations</span><button class="isup-btn-primary" @click="openAssetModal()"><i class="bi-plus-lg me-1"></i>Nouveau bien</button></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="assetsLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!assets.length" class="text-center py-5 isup-empty-cell"><i class="bi-building" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucune immobilisation.</p><button class="isup-btn-primary" @click="openAssetModal()">Ajouter un bien</button></div>
                            <div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Désignation</th><th>Catégorie</th><th>Acquisition</th><th class="text-end">Valeur brute</th><th class="text-end">Dotation/mois</th><th class="text-end">VCN</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead><tbody><tr v-for="a in assets" :key="a.id"><td class="ps-4 fw-semibold">{{ a.designation }}</td><td><span class="isup-badge isup-badge-light">{{ a.category }}</span></td><td>{{ formatDate(a.acquisition_date) }}</td><td class="text-end">{{ formatCurrency(a.gross_value) }}</td><td class="text-end">{{ formatCurrency(a.monthly_depr) }}</td><td class="text-end fw-semibold">{{ formatCurrency(a.net_book_value) }}</td><td><span class="isup-status" :class="statusBadge(a.status)">{{ statusLabel(a.status) }}</span></td><td class="text-end pe-4"><button class="isup-icon-btn me-1" title="Détail" @click="loadAssetDetail(a.id)"><i class="bi-eye"></i></button><button class="isup-icon-btn me-1" title="Plan amort." @click="loadAssetDetail(a.id)"><i class="bi-calendar-range"></i></button><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteAsset(a.id)"><i class="bi-trash"></i></button></td></tr></tbody></table></div>
                        </div>
                    </div>
                </div>

                <!-- ==================== EXERCICES FISCAUX ==================== -->
                <div v-if="activeTab==='fiscal-years'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center"><span><i class="bi-calendar3 me-2" style="color:#FF7900;"></i>Exercices comptables</span><button class="isup-btn-primary" @click="openFyModal()"><i class="bi-plus-lg me-1"></i>Nouvel exercice</button></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="fyLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!fiscalYears.length" class="text-center py-5 isup-empty-cell"><i class="bi-calendar3" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucun exercice.</p><button class="isup-btn-primary" @click="openFyModal()">Créer un exercice</button></div>
                            <div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Année</th><th>Début</th><th>Fin</th><th>Statut</th><th>Clôturé le</th><th>Par</th><th class="text-end pe-4">Actions</th></tr></thead><tbody><tr v-for="fy in fiscalYears" :key="fy.id"><td class="ps-4 fw-bold">{{ fy.year }}</td><td>{{ formatDate(fy.date_start) }}</td><td>{{ formatDate(fy.date_end) }}</td><td><span class="isup-status" :class="statusBadge(fy.status)">{{ statusLabel(fy.status) }}</span></td><td>{{ fy.closed_at||'-' }}</td><td>{{ fy.closed_by||'-' }}</td><td class="text-end pe-4"><button v-if="fy.status==='open'" class="isup-icon-btn me-1" style="color:#e65100;" title="Clôturer" @click="openFyCloseModal(fy)"><i class="bi-lock"></i></button><button v-if="fy.status==='closed'" class="isup-icon-btn me-1" style="color:#2e7d32;" title="Réouvrir" @click="reopenFy(fy.id)"><i class="bi-unlock"></i></button><button v-if="fy.status==='open'||fy.status==='closed'" class="isup-icon-btn me-1" title="Verrouiller" @click="lockFy(fy.id)"><i class="bi-shield-lock"></i></button></td></tr></tbody></table></div>
                        </div>
                    </div>
                </div>

                <!-- ==================== RÉCONCILIATION ==================== -->
                <div v-if="activeTab==='reconciliation'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center"><span><i class="bi-arrow-left-right me-2" style="color:#FF7900;"></i>Réconciliation bancaire</span><button class="isup-btn-primary" @click="openRecoModal()"><i class="bi-plus-lg me-1"></i>Nouvelle réconciliation</button></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="recoLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!reconciliations.length" class="text-center py-5 isup-empty-cell"><i class="bi-arrow-left-right" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i><p>Aucune réconciliation.</p><button class="isup-btn-primary" @click="openRecoModal()">Créer</button></div>
                            <div v-else class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th class="ps-4">Période</th><th>Compte bancaire</th><th>Banque</th><th class="text-end">Solde relevé</th><th class="text-end">Solde livres</th><th class="text-end">Écart</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead><tbody><tr v-for="r in reconciliations" :key="r.id"><td class="ps-4 fw-semibold">{{ r.period }}</td><td>{{ r.bank_account }}</td><td>{{ r.bank_name||'-' }}</td><td class="text-end">{{ formatCurrency(r.balance_per_statement) }}</td><td class="text-end">{{ formatCurrency(r.balance_per_books) }}</td><td class="text-end fw-semibold" :style="{color:r.difference!==0?'#c62828':'#2e7d32'}">{{ formatCurrency(r.difference) }}</td><td><span class="isup-status" :class="statusBadge(r.status)">{{ statusLabel(r.status) }}</span></td><td class="text-end pe-4"><button v-if="r.status==='draft'" class="isup-icon-btn me-1" style="color:#2e7d32;" title="Rapprocher" @click="matchReco(r.id)"><i class="bi-check2"></i></button><button v-if="r.status==='matched'" class="isup-icon-btn me-1" style="color:#1565c0;" title="Approuver" @click="approveReco(r.id)"><i class="bi-check2-all"></i></button><button v-if="r.status==='draft'" class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteReco(r.id)"><i class="bi-trash"></i></button></td></tr></tbody></table></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== MODAL COMPTE ==================== -->
        <div v-if="showAccountModal" class="isup-modal-overlay" @click.self="showAccountModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-book me-2"></i>{{ editingAccount?'Modifier':'Nouveau' }} compte</span><button class="isup-modal-close" @click="showAccountModal=false">&times;</button></div>
                <form @submit.prevent="submitAccount">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="isup-label">Code *</label><input type="text" class="isup-input" v-model="accountForm.code" placeholder="Ex: 401000" required></div>
                            <div class="col-md-8"><label class="isup-label">Libellé *</label><input type="text" class="isup-input" v-model="accountForm.name" placeholder="Nom du compte" required></div>
                            <div class="col-md-6"><label class="isup-label">Classe SYSCOHADA</label><select class="isup-select" v-model="accountForm.type"><option v-for="t in accountTypes" :key="t.value" :value="t.value">{{ t.label }}</option></select></div>
                            <div class="col-md-6 d-flex align-items-end gap-3"><label class="isup-switch"><input type="checkbox" v-model="accountForm.is_active"><span class="isup-switch-slider"></span></label><span style="font-size:12px;">Compte actif</span></div>
                            <div class="col-md-6 d-flex align-items-end gap-3"><label class="isup-switch"><input type="checkbox" v-model="accountForm.has_tva"><span class="isup-switch-slider"></span></label><span style="font-size:12px;">Assujetti TVA</span></div>
                            <div v-if="accountForm.has_tva" class="col-md-6"><label class="isup-label">Taux TVA (%)</label><input type="number" step="0.1" class="isup-input" v-model="accountForm.tva_rate"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showAccountModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="accountSubmitting"><span v-if="accountSubmitting" class="isup-spinner-sm me-1"></span>{{ editingAccount?'Enregistrer':'Créer' }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL JOURNAL ==================== -->
        <div v-if="showJournalModal" class="isup-modal-overlay" @click.self="showJournalModal=false">
            <div class="isup-modal isup-modal-xl">
                <div class="isup-modal-header"><span><i class="bi-journal-text me-2"></i>Nouvelle écriture comptable</span><button class="isup-modal-close" @click="showJournalModal=false">&times;</button></div>
                <form @submit.prevent="submitJournal">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3"><label class="isup-label">Type journal</label><select class="isup-select" v-model="journalForm.journal_type"><option value="achat">Achats</option><option value="vente">Ventes</option><option value="banque">Banque</option><option value="caisse">Caisse</option><option value="operations_diverses">Opérations diverses</option><option value="od">OD</option><option value="salaire">Salaires</option><option value="investissement">Investissement</option></select></div>
                            <div class="col-md-3"><label class="isup-label">Date *</label><input type="date" class="isup-input" v-model="journalForm.entry_date" required></div>
                            <div class="col-md-3"><label class="isup-label">Référence</label><input type="text" class="isup-input" v-model="journalForm.reference" placeholder="Auto si vide"></div>
                            <div class="col-md-3"><label class="isup-label">État</label><div><span class="isup-status" :class="journalIsBalanced?'isup-status-green':'isup-status-red'"><i :class="journalIsBalanced?'bi-check-circle':'bi-exclamation-triangle'" class="me-1"></i>{{ journalIsBalanced?'Équilibré':'Déséquilibré' }}</span></div></div>
                            <div class="col-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="2" v-model="journalForm.description" placeholder="Description de l'écriture..."></textarea></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px;font-weight:700;color:#163A5E;">Lignes d'écriture</span><button type="button" class="isup-btn-grey" @click="addJournalLine"><i class="bi-plus-lg me-1"></i>Ajouter ligne</button></div>
                        <div class="isup-table-wrap"><table class="isup-table isup-table-bordered w-100"><thead><tr><th style="width:25%;">Compte</th><th style="width:30%;">Libellé</th><th style="width:15%;">Débit</th><th style="width:15%;">Crédit</th><th style="width:2%;"></th></tr></thead><tbody><tr v-for="(line,index) in journalForm.lines" :key="index"><td><select class="isup-select" v-model="line.account_id" required><option value="" disabled>Sélectionner</option><option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.code }} - {{ acc.name }}</option></select></td><td><input type="text" class="isup-input" v-model="line.label" placeholder="Libellé" required></td><td><input type="number" step="0.01" min="0" class="isup-input" v-model.number="line.debit" placeholder="0,00"></td><td><input type="number" step="0.01" min="0" class="isup-input" v-model.number="line.credit" placeholder="0,00"></td><td><button type="button" class="isup-icon-btn isup-icon-danger" @click="removeJournalLine(index)" :disabled="journalForm.lines.length<=2"><i class="bi-x-lg"></i></button></td></tr></tbody><tfoot style="background:#EEF3F9;font-weight:700;"><tr><td colspan="2" class="text-end">Totaux :</td><td class="text-end">{{ formatCurrency(journalTotalDebit) }}</td><td class="text-end">{{ formatCurrency(journalTotalCredit) }}</td><td></td></tr></tfoot></table></div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showJournalModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="journalSubmitting||!journalIsBalanced"><span v-if="journalSubmitting" class="isup-spinner-sm me-1"></span>Créer l'écriture</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL DÉTAIL JOURNAL ==================== -->
        <div v-if="showJournalDetailModal" class="isup-modal-overlay" @click.self="showJournalDetailModal=false">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header"><span>Écriture {{ selectedJournal?.reference }}</span><button class="isup-modal-close" @click="showJournalDetailModal=false">&times;</button></div>
                <div class="isup-modal-body" v-if="selectedJournal">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4"><small class="isup-detail-label">Type</small><div class="fw-semibold">{{ journalTypeLabel(selectedJournal.journal_type) }}</div></div>
                        <div class="col-md-4"><small class="isup-detail-label">Date</small><div>{{ formatDate(selectedJournal.entry_date) }}</div></div>
                        <div class="col-md-4"><small class="isup-detail-label">Statut</small><div><span class="isup-status" :class="statusBadge(selectedJournal.status)">{{ statusLabel(selectedJournal.status) }}</span></div></div>
                        <div class="col-md-4"><small class="isup-detail-label">Créé par</small><div class="fw-semibold">{{ selectedJournal.created_by }}</div></div>
                        <div class="col-md-4"><small class="isup-detail-label">N° Pièce</small><div class="fw-semibold">{{ selectedJournal.numero_piece||'-' }}</div></div>
                        <div class="col-md-4"><small class="isup-detail-label">Extourne</small><div>{{ selectedJournal.is_reversal?'Oui':'Non' }}</div></div>
                        <div v-if="selectedJournal.description" class="col-12"><small class="isup-detail-label">Description</small><div>{{ selectedJournal.description }}</div></div>
                    </div>
                    <div style="font-size:12px;font-weight:700;color:#163A5E;margin-bottom:8px;">Lignes d'écriture</div>
                    <div class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th>Compte</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th></tr></thead><tbody><tr v-for="l in selectedJournal.lines" :key="l.id"><td class="fw-semibold">{{ l.account_code }} - {{ l.account_name }}</td><td>{{ l.label }}</td><td class="text-end">{{ l.debit?formatCurrency(l.debit):'-' }}</td><td class="text-end">{{ l.credit?formatCurrency(l.credit):'-' }}</td></tr></tbody><tfoot style="background:#EEF3F9;font-weight:700;"><tr><td colspan="2" class="text-end">Totaux :</td><td class="text-end">{{ formatCurrency(selectedJournal.debit_total) }}</td><td class="text-end">{{ formatCurrency(selectedJournal.credit_total) }}</td></tr></tfoot></table></div>
                </div>
                <div class="isup-modal-footer"><button type="button" class="isup-btn-grey" @click="showJournalDetailModal=false">Fermer</button></div>
            </div>
        </div>

        <!-- ==================== MODAL TVA ==================== -->
        <div v-if="showTvaModal" class="isup-modal-overlay" @click.self="showTvaModal=false">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header"><span><i class="bi-receipt me-2"></i>Nouvelle déclaration TVA</span><button class="isup-modal-close" @click="showTvaModal=false">&times;</button></div>
                <form @submit.prevent="submitTva">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4"><label class="isup-label">Période</label><input type="month" class="isup-input" v-model="tvaPeriod"></div>
                            <div class="col-md-4"><label class="isup-label">Type</label><select class="isup-select" v-model="tvaForm.type"><option value="monthly">Mensuelle</option><option value="quarterly">Trimestrielle</option><option value="annual">Annuelle</option></select></div>
                            <div class="col-md-4 d-flex align-items-end"><button type="button" class="isup-btn-grey w-100" @click="computeTva" :disabled="tvaSubmitting"><i class="bi-calculator me-1"></i>Calculer auto</button></div>
                        </div>
                        <div v-if="tvaComputed" class="isup-info-box mb-3"><i class="bi-info-circle me-2"></i>{{ tvaComputed.lines_count }} lignes de TVA trouvées.</div>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="isup-label">TVA collectée</label><input type="number" step="0.01" class="isup-input" v-model="tvaForm.tva_collected" required></div>
                            <div class="col-md-4"><label class="isup-label">TVA déductible</label><input type="number" step="0.01" class="isup-input" v-model="tvaForm.tva_deductible" required></div>
                            <div class="col-md-4"><label class="isup-label">TVA nette</label><input type="number" step="0.01" class="isup-input" :value="parseFloat(tvaForm.tva_collected)-parseFloat(tvaForm.tva_deductible)" readonly></div>
                            <div class="col-12"><label class="isup-label">Notes</label><textarea class="isup-input" rows="2" v-model="tvaForm.notes"></textarea></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showTvaModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="tvaSubmitting"><span v-if="tvaSubmitting" class="isup-spinner-sm me-1"></span>Créer la déclaration</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL IMMOBILISATION ==================== -->
        <div v-if="showAssetModal" class="isup-modal-overlay" @click.self="showAssetModal=false">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header"><span><i class="bi-building me-2"></i>Nouvelle immobilisation</span><button class="isup-modal-close" @click="showAssetModal=false">&times;</button></div>
                <form @submit.prevent="submitAsset">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-12"><label class="isup-label">Désignation *</label><input type="text" class="isup-input" v-model="assetForm.designation" required></div>
                            <div class="col-md-4"><label class="isup-label">Catégorie</label><select class="isup-select" v-model="assetForm.category"><option value="informatique">Informatique</option><option value="mobilier">Mobilier</option><option value="vehicule">Véhicule</option><option value="batiment">Bâtiment</option><option value="logiciel">Logiciel</option><option value="other">Autre</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Date acquisition *</label><input type="date" class="isup-input" v-model="assetForm.acquisition_date" required></div>
                            <div class="col-md-4"><label class="isup-label">Méthode amort.</label><select class="isup-select" v-model="assetForm.depreciation_method"><option value="linear">Linéaire</option><option value="declining">Dégressif</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Valeur brute *</label><input type="number" step="0.01" min="0" class="isup-input" v-model="assetForm.gross_value" required></div>
                            <div class="col-md-4"><label class="isup-label">Valeur résiduelle</label><input type="number" step="0.01" min="0" class="isup-input" v-model="assetForm.residual_value"></div>
                            <div class="col-md-4"><label class="isup-label">Durée (mois) *</label><input type="number" min="1" class="isup-input" v-model="assetForm.depreciation_months" required></div>
                            <div class="col-12"><label class="isup-label">Notes</label><textarea class="isup-input" rows="2" v-model="assetForm.notes"></textarea></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showAssetModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="assetSubmitting"><span v-if="assetSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL DÉTAIL IMMOBILISATION ==================== -->
        <div v-if="showAssetDetailModal" class="isup-modal-overlay" @click.self="showAssetDetailModal=false">
            <div class="isup-modal isup-modal-xl">
                <div class="isup-modal-header"><span>{{ selectedAsset?.asset?.designation||'Détail' }}</span><button class="isup-modal-close" @click="showAssetDetailModal=false">&times;</button></div>
                <div class="isup-modal-body" v-if="selectedAsset">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3"><small class="isup-detail-label">Catégorie</small><div class="fw-semibold">{{ selectedAsset.asset.category }}</div></div>
                        <div class="col-md-3"><small class="isup-detail-label">Valeur brute</small><div class="fw-semibold">{{ formatCurrency(selectedAsset.asset.gross_value) }}</div></div>
                        <div class="col-md-3"><small class="isup-detail-label">VCN</small><div class="fw-semibold">{{ formatCurrency(selectedAsset.asset.net_book_value) }}</div></div>
                        <div class="col-md-3"><small class="isup-detail-label">Statut</small><div><span class="isup-status" :class="statusBadge(selectedAsset.asset.status)">{{ selectedAsset.asset.status }}</span></div></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px;font-weight:700;color:#163A5E;">Plan d'amortissement</span><button class="isup-btn-primary" @click="generateSchedule(selectedAsset.asset.id)"><i class="bi-calendar-range me-1"></i>Générer</button></div>
                    <div class="isup-table-wrap"><table class="isup-table w-100"><thead><tr><th>N°</th><th>Date</th><th class="text-end">Dotation</th><th class="text-end">Cumul</th><th class="text-end">VCN</th></tr></thead><tbody><tr v-for="s in selectedAsset.schedule" :key="s.id"><td>{{ s.period_number }}</td><td>{{ formatDate(s.period_date) }}</td><td class="text-end">{{ formatCurrency(s.amount) }}</td><td class="text-end">{{ formatCurrency(s.accumulated) }}</td><td class="text-end fw-semibold">{{ formatCurrency(s.net_value) }}</td></tr></tbody></table></div>
                </div>
                <div class="isup-modal-footer"><button type="button" class="isup-btn-grey" @click="showAssetDetailModal=false">Fermer</button></div>
            </div>
        </div>

        <!-- ==================== MODAL EXERCICE ==================== -->
        <div v-if="showFyModal" class="isup-modal-overlay" @click.self="showFyModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-calendar3 me-2"></i>Nouvel exercice</span><button class="isup-modal-close" @click="showFyModal=false">&times;</button></div>
                <form @submit.prevent="submitFy">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="isup-label">Année *</label><input type="number" min="2000" max="2100" class="isup-input" v-model="fyForm.year" required></div>
                            <div class="col-md-4"><label class="isup-label">Date début *</label><input type="date" class="isup-input" v-model="fyForm.date_start" required></div>
                            <div class="col-md-4"><label class="isup-label">Date fin *</label><input type="date" class="isup-input" v-model="fyForm.date_end" required></div>
                            <div class="col-12"><label class="isup-label">Notes</label><textarea class="isup-input" rows="2" v-model="fyForm.notes"></textarea></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showFyModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="fySubmitting"><span v-if="fySubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL CLÔTURE EXERCICE ==================== -->
        <div v-if="showFyCloseModal" class="isup-modal-overlay" @click.self="showFyCloseModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-lock me-2"></i>Clôture exercice {{ selectedFy?.year }}</span><button class="isup-modal-close" @click="showFyCloseModal=false">&times;</button></div>
                <form @submit.prevent="submitFyClose">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <p style="font-size:12px;color:#888;margin-bottom:8px;">Cochez les vérifications effectuées :</p>
                        <div class="isup-check-item"><label class="isup-switch"><input type="checkbox" v-model="fyCloseForm.check_balance"><span class="isup-switch-slider"></span></label><span style="font-size:12px;">Balance vérifiée (débit = crédit)</span></div>
                        <div class="isup-check-item"><label class="isup-switch"><input type="checkbox" v-model="fyCloseForm.check_tva"><span class="isup-switch-slider"></span></label><span style="font-size:12px;">TVA déclarée et payée</span></div>
                        <div class="isup-check-item"><label class="isup-switch"><input type="checkbox" v-model="fyCloseForm.check_cnss"><span class="isup-switch-slider"></span></label><span style="font-size:12px;">CNSS à jour</span></div>
                        <div class="isup-check-item"><label class="isup-switch"><input type="checkbox" v-model="fyCloseForm.check_reconciliation"><span class="isup-switch-slider"></span></label><span style="font-size:12px;">Réconciliation bancaire effectuée</span></div>
                        <div class="isup-check-item"><label class="isup-switch"><input type="checkbox" v-model="fyCloseForm.check_inventory"><span class="isup-switch-slider"></span></label><span style="font-size:12px;">Inventaire validé</span></div>
                        <div class="mt-3"><label class="isup-label">Notes de clôture</label><textarea class="isup-input" rows="2" v-model="fyCloseForm.notes"></textarea></div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showFyCloseModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" style="background:#e65100;" :disabled="fySubmitting"><span v-if="fySubmitting" class="isup-spinner-sm me-1"></span><i class="bi-lock me-1"></i>Clôturer l'exercice</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL RÉCONCILIATION ==================== -->
        <div v-if="showRecoModal" class="isup-modal-overlay" @click.self="showRecoModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-arrow-left-right me-2"></i>Nouvelle réconciliation</span><button class="isup-modal-close" @click="showRecoModal=false">&times;</button></div>
                <form @submit.prevent="submitReco">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">Compte bancaire *</label><input type="text" class="isup-input" v-model="recoForm.bank_account" required></div>
                            <div class="col-md-6"><label class="isup-label">Banque</label><input type="text" class="isup-input" v-model="recoForm.bank_name"></div>
                            <div class="col-md-6"><label class="isup-label">Période *</label><input type="month" class="isup-input" v-model="recoForm.period" required></div>
                            <div class="col-md-6"><label class="isup-label">Date relevé *</label><input type="date" class="isup-input" v-model="recoForm.statement_date" required></div>
                            <div class="col-md-6"><label class="isup-label">Solde relevé *</label><input type="number" step="0.01" class="isup-input" v-model="recoForm.balance_per_statement" required></div>
                            <div class="col-md-6"><label class="isup-label">Solde comptable *</label><input type="number" step="0.01" class="isup-input" v-model="recoForm.balance_per_books" required></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showRecoModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="recoSubmitting"><span v-if="recoSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </CompanyLayout>
</template>
