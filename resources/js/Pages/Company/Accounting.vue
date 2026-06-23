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

// Modules domaine activés pour ce client
const activeModules = computed(() => {
    try { return window.__ACCOUNTING_MODULES__ || []; } catch(e) { return []; }
});
const hasModule = (mod) => activeModules.value.includes(mod);

const baseTabs = [
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
    { key: 'treasury', label: 'Trésorerie', icon: 'bi-cash-stack' },
    { key: 'grilles-tarifaires', label: 'Grilles tarifaires', icon: 'bi-tags' },
    { key: 'commissions', label: 'Commissions', icon: 'bi-percent' },
    { key: 'mobile-money', label: 'Mobile Money', icon: 'bi-phone' },
    { key: 'rapports', label: 'Rapports', icon: 'bi-file-earmark-pdf' },
];

const metierTabsDef = [
    { key: 'invoices', label: 'Facturation', icon: 'bi-receipt-cutoff', module: 'facturation' },
    { key: 'stock', label: 'Stock', icon: 'bi-box-seam', module: 'stock' },
    { key: 'arrivages', label: 'Arrivages', icon: 'bi-truck', module: 'arrivage' },
    { key: 'commandes', label: 'Commandes', icon: 'bi-cart3', module: 'stock' },
    { key: 'emballages', label: 'Emballages', icon: 'bi-box', module: 'emballage' },
    { key: 'hotel-chambres', label: 'Chambres', icon: 'bi-door-open', module: 'gestion_chambres' },
    { key: 'hotel-reservations', label: 'Réservations', icon: 'bi-calendar-check', module: 'gestion_chambres' },
    { key: 'hotel-factures', label: 'Fact. Hôtel', icon: 'bi-receipt', module: 'facturation_nuitee' },
    { key: 'scolarite-factures', label: 'Fact. Scolarité', icon: 'bi-mortarboard', module: 'facturation_scolarite' },
    { key: 'scolarite-eleves', label: 'Élèves', icon: 'bi-people', module: 'gestion_eleves' },
    { key: 'quittances', label: 'Quittances', icon: 'bi-file-text', module: 'quittances_loyer' },
    { key: 'biens-location', label: 'Biens', icon: 'bi-building', module: 'gestion_biens' },
    { key: 'loyers-impayes', label: 'Loyers impayés', icon: 'bi-exclamation-triangle', module: 'loyers_impayes' },
    { key: 'tontines', label: 'Tontines', icon: 'bi-people', module: 'gestion_tontines' },
    { key: 'cotisations', label: 'Cotisations', icon: 'bi-coin', module: 'cotisations' },
    { key: 'pressing-commandes', label: 'Commandes pressing', icon: 'bi-basket', module: 'commandes_pressing' },
    { key: 'transit-fret', label: 'Fact. Fret', icon: 'bi-truck', module: 'facturation_fret' },
    { key: 'transit-dossiers', label: 'Dossiers transit', icon: 'bi-folder', module: 'transit_dossiers' },
    { key: 'morgue-depots', label: 'Dépôts morgue', icon: 'bi-plus-square', module: 'gestion_depots' },
    { key: 'morgue-factures', label: 'Fact. Morgue', icon: 'bi-receipt', module: 'facturation_morgue' },
    { key: 'taxe-nuitee', label: 'Taxe de séjour', icon: 'bi-cash-stack', module: 'taxe_nuitee' },
];

const tabs = computed(() => {
    const filtered = [...baseTabs];
    metierTabsDef.forEach(t => {
        if (hasModule(t.module)) {
            filtered.push(t);
        }
    });
    return filtered;
});

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

// ═══════════════════════════════════════════════════════════════════
// Stock — API & Data
// ═══════════════════════════════════════════════════════════════════
const stockItems = ref([]);
const stockLoading = ref(false);
const showStockModal = ref(false);
const stockSubmitting = ref(false);
const showImportStock = ref(false);
const importStockFile = ref(null);
const importStockError = ref('');
const importStockSuccess = ref('');
const stockForm = ref({reference:'',designation:'',purchase_price:'',selling_price:'',stock_alert:0,unit:'unite'});

const loadStockItems = async () => {
    stockLoading.value=true;
    try{const r=await fetch('/api/company/accounting/stock/items');if(r.ok)stockItems.value=await r.json();else throw Error('Erreur');
    }catch(e){error.value=e.message}finally{stockLoading.value=false}
};
const openStockModal = () => {
    stockForm.value={reference:'',designation:'',purchase_price:'',selling_price:'',stock_alert:0,unit:'unite'};
    showStockModal.value=true;
};
const submitStockItem = async () => {
    stockSubmitting.value=true;error.value=null;
    try{const r=await fetch('/api/company/accounting/stock/items',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(stockForm.value)});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;showStockModal.value=false;await loadStockItems();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}finally{stockSubmitting.value=false}
};
const deleteStockItem = async (id) => {
    if(!confirm('Supprimer cet article ?'))return;
    try{const r=await fetch(`/api/company/accounting/stock/items/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;await loadStockItems();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}
};
const importStockItems = async () => {
    if(!importStockFile.value){importStockError.value='Sélectionnez un fichier CSV.';return}
    importStockError.value='';importStockSuccess.value='';
    const fd=new FormData();fd.append('csv',importStockFile.value);
    try{const r=await fetch('/api/company/accounting/stock/import',{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:fd});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        importStockSuccess.value=d.message;await loadStockItems();
    }catch(e){importStockError.value=e.message}
};
const totalStockValue = computed(() => stockItems.value.reduce((s,i)=>s+(i.stock||0)*(i.purchase_price||0),0));

// ═══════════════════════════════════════════════════════════════════
// Emballages consignés
// ═══════════════════════════════════════════════════════════════════
const emballages = ref([]);
const emballagesLoading = ref(false);
const showEmballageModal = ref(false);
const emballageSubmitting = ref(false);
const emballageForm = ref({type:'consigne',tiers_nom:'',tiers_type:'client',produit:'',quantite:1,montant_consigne:0,date_emission:new Date().toISOString().split('T')[0],date_retour:'',statut:'en_cours',notes:''});

const loadEmballages = async () => {
    emballagesLoading.value=true;
    try{const r=await fetch('/api/company/accounting/emballages');if(r.ok)emballages.value=await r.json();
    }catch(e){error.value=e.message}finally{emballagesLoading.value=false}
};
const openEmballageModal = () => {
    emballageForm.value={type:'consigne',tiers_nom:'',tiers_type:'client',produit:'',quantite:1,montant_consigne:0,date_emission:new Date().toISOString().split('T')[0],date_retour:'',statut:'en_cours',notes:''};
    showEmballageModal.value=true;
};
const submitEmballage = async () => {
    emballageSubmitting.value=true;error.value=null;
    try{const r=await fetch('/api/company/accounting/emballages',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(emballageForm.value)});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;showEmballageModal.value=false;await loadEmballages();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}finally{emballageSubmitting.value=false}
};
const deleteEmballage = async (id) => {
    if(!confirm('Supprimer ?'))return;
    try{const r=await fetch(`/api/company/accounting/emballages/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;await loadEmballages();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}
};

// ═══════════════════════════════════════════════════════════════════
// Stock — Mouvements (entrées / sorties)
// ═══════════════════════════════════════════════════════════════════
const movements = ref([]);
const showMovementModal = ref(false);
const movementType = ref('entry');
const movementSubmitting = ref(false);
const movementForm = ref({erp_item_id:'',type:'entry',quantity:1,motif:'',movement_date:new Date().toISOString().split('T')[0]});

const entries = computed(() => movements.value.filter(m => m.type==='entry'));
const exits = computed(() => movements.value.filter(m => m.type==='exit'));

const loadMovements = async () => {
    try{const r=await fetch('/api/company/accounting/stock/movements');if(r.ok)movements.value=await r.json();
    }catch(e){console.error(e)}
};
const openMovementModal = (type) => {
    movementType.value=type;
    movementForm.value={erp_item_id:'',type,quantity:1,motif:'',movement_date:new Date().toISOString().split('T')[0]};
    showMovementModal.value=true;
};
const submitMovement = async () => {
    movementSubmitting.value=true;error.value=null;
    try{const r=await fetch('/api/company/accounting/stock/movements',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(movementForm.value)});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;showMovementModal.value=false;await loadMovements();await loadStockItems();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}finally{movementSubmitting.value=false}
};

// ═══════════════════════════════════════════════════════════════════
// Hôtel — Factures
// ═══════════════════════════════════════════════════════════════════
const hotelFactures = ref([]);
const hotelFacturesLoading = ref(false);
const showHotelFactureModal = ref(false);
const hotelFactureSubmitting = ref(false);
const hotelFactureForm = ref({numero_facture:'',type:'chambre',client_nom:'',chambre:'',date_arrivee:'',date_depart:'',nb_nuitees:1,prix_nuitee:0,montant_ht:0,tva:0,taxe_sejour:0,montant_ttc:0,montant_paye:0,statut:'en_attente'});

const loadHotelFactures = async () => {
    hotelFacturesLoading.value=true;
    try{const r=await fetch('/api/company/accounting/hotel/factures');if(r.ok)hotelFactures.value=await r.json();
    }catch(e){error.value=e.message}finally{hotelFacturesLoading.value=false}
};
const openHotelFactureModal = () => {
    hotelFactureForm.value={numero_facture:'FACT-H-'+Date.now(),type:'chambre',client_nom:'',chambre:'',date_arrivee:'',date_depart:'',nb_nuitees:1,prix_nuitee:0,montant_ht:0,tva:0,taxe_sejour:0,montant_ttc:0,montant_paye:0,statut:'en_attente'};
    showHotelFactureModal.value=true;
};
const submitHotelFacture = async () => {
    hotelFactureSubmitting.value=true;error.value=null;
    try{const r=await fetch('/api/company/accounting/hotel/factures',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(hotelFactureForm.value)});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;showHotelFactureModal.value=false;await loadHotelFactures();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}finally{hotelFactureSubmitting.value=false}
};
const deleteHotelFacture = async (id) => {
    if(!confirm('Supprimer cette facture ?'))return;
    try{const r=await fetch(`/api/company/accounting/hotel/factures/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;await loadHotelFactures();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}
};

// ═══════════════════════════════════════════════════════════════════
// Scolaire — Factures
// ═══════════════════════════════════════════════════════════════════
const scolaireFactures = ref([]);
const scolaireFacturesLoading = ref(false);
const showScolaireFactureModal = ref(false);
const scolaireFactureSubmitting = ref(false);
const scolaireFactureForm = ref({numero_facture:'',annee_scolaire:'',eleve_nom:'',eleve_prenom:'',classe:'',matricule:'',type_frais:'scolarite',periode:'',montant_du:0,remise:0,montant_net:0,montant_paye:0,statut:'en_attente',date_echeance:''});

const loadScolaireFactures = async () => {
    scolaireFacturesLoading.value=true;
    try{const r=await fetch('/api/company/accounting/scolaire/factures');if(r.ok)scolaireFactures.value=await r.json();
    }catch(e){error.value=e.message}finally{scolaireFacturesLoading.value=false}
};
const openScolaireFactureModal = () => {
    scolaireFactureForm.value={numero_facture:'FACT-S-'+Date.now(),annee_scolaire:new Date().getFullYear()+'/'+(new Date().getFullYear()+1),eleve_nom:'',eleve_prenom:'',classe:'',matricule:'',type_frais:'scolarite',periode:'',montant_du:0,remise:0,montant_net:0,montant_paye:0,statut:'en_attente',date_echeance:''};
    showScolaireFactureModal.value=true;
};
const submitScolaireFacture = async () => {
    scolaireFactureSubmitting.value=true;error.value=null;
    try{const r=await fetch('/api/company/accounting/scolaire/factures',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(scolaireFactureForm.value)});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;showScolaireFactureModal.value=false;await loadScolaireFactures();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}finally{scolaireFactureSubmitting.value=false}
};
const deleteScolaireFacture = async (id) => {
    if(!confirm('Supprimer cette facture ?'))return;
    try{const r=await fetch(`/api/company/accounting/scolaire/factures/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;await loadScolaireFactures();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}
};

// ═══════════════════════════════════════════════════════════════════
// Location — Quittances
// ═══════════════════════════════════════════════════════════════════
const quittances = ref([]);
const quittancesLoading = ref(false);
const showQuittanceModal = ref(false);
const quittanceSubmitting = ref(false);
const quittanceForm = ref({numero_quittance:'',bien:'',locataire_nom:'',periode:'',date_debut:'',date_fin:'',loyer_ht:0,charges:0,tva:0,montant_total:0,montant_paye:0,statut:'en_attente'});

const loadQuittances = async () => {
    quittancesLoading.value=true;
    try{const r=await fetch('/api/company/accounting/location/quittances');if(r.ok)quittances.value=await r.json();
    }catch(e){error.value=e.message}finally{quittancesLoading.value=false}
};
const openQuittanceModal = () => {
    const now=new Date();const month=now.toISOString().slice(0,7);
    quittanceForm.value={numero_quittance:'QUIT-'+Date.now(),bien:'',locataire_nom:'',periode:month,date_debut:'',date_fin:'',loyer_ht:0,charges:0,tva:0,montant_total:0,montant_paye:0,statut:'en_attente'};
    showQuittanceModal.value=true;
};
const submitQuittance = async () => {
    quittanceSubmitting.value=true;error.value=null;
    try{const r=await fetch('/api/company/accounting/location/quittances',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(quittanceForm.value)});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;showQuittanceModal.value=false;await loadQuittances();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}finally{quittanceSubmitting.value=false}
};
const deleteQuittance = async (id) => {
    if(!confirm('Supprimer cette quittance ?'))return;
    try{const r=await fetch(`/api/company/accounting/location/quittances/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;await loadQuittances();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}
};

// ═══════════════════════════════════════════════════════════════════
// Tontine — Cotisations
// ═══════════════════════════════════════════════════════════════════
const cotisations = ref([]);
const cotisationsLoading = ref(false);
const showCotisationModal = ref(false);
const cotisationSubmitting = ref(false);
const cotisationForm = ref({tontine_nom:'',membre_nom:'',periode:'',date_echeance:'',montant:0,montant_paye:0,statut:'en_attente'});

const loadCotisations = async () => {
    cotisationsLoading.value=true;
    try{const r=await fetch('/api/company/accounting/tontine/cotisations');if(r.ok)cotisations.value=await r.json();
    }catch(e){error.value=e.message}finally{cotisationsLoading.value=false}
};
const openCotisationModal = () => {
    cotisationForm.value={tontine_nom:'',membre_nom:'',periode:new Date().toISOString().slice(0,7),date_echeance:'',montant:0,montant_paye:0,statut:'en_attente'};
    showCotisationModal.value=true;
};
const submitCotisation = async () => {
    cotisationSubmitting.value=true;error.value=null;
    try{const r=await fetch('/api/company/accounting/tontine/cotisations',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(cotisationForm.value)});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;showCotisationModal.value=false;await loadCotisations();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}finally{cotisationSubmitting.value=false}
};
const deleteCotisation = async (id) => {
    if(!confirm('Supprimer cette cotisation ?'))return;
    try{const r=await fetch(`/api/company/accounting/tontine/cotisations/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;await loadCotisations();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}
};

// ═══════════════════════════════════════════════════════════════════
// Transport — Transit Dossiers
// ═══════════════════════════════════════════════════════════════════
const transitDossiers = ref([]);
const transitLoading = ref(false);
const showTransitModal = ref(false);
const transitSubmitting = ref(false);
const transitForm = ref({reference_dossier:'',type_transit:'import',fournisseur_nom:'',marchandise:'',valeur_marchandise:0,fret_ht:0,droits_douane:0,tva_douane:0,frais_accessoires:0,montant_paye:0,statut:'en_cours',date_ouverture:'',douane_bureau:''});

const loadTransitDossiers = async () => {
    transitLoading.value=true;
    try{const r=await fetch('/api/company/accounting/transport/transit');if(r.ok)transitDossiers.value=await r.json();
    }catch(e){error.value=e.message}finally{transitLoading.value=false}
};
const openTransitModal = () => {
    transitForm.value={reference_dossier:'DOS-'+Date.now(),type_transit:'import',fournisseur_nom:'',marchandise:'',valeur_marchandise:0,fret_ht:0,droits_douane:0,tva_douane:0,frais_accessoires:0,montant_paye:0,statut:'en_cours',date_ouverture:new Date().toISOString().split('T')[0],douane_bureau:''};
    showTransitModal.value=true;
};
const submitTransit = async () => {
    transitSubmitting.value=true;error.value=null;
    try{const r=await fetch('/api/company/accounting/transport/transit',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(transitForm.value)});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;showTransitModal.value=false;await loadTransitDossiers();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}finally{transitSubmitting.value=false}
};
const deleteTransit = async (id) => {
    if(!confirm('Supprimer ce dossier ?'))return;
    try{const r=await fetch(`/api/company/accounting/transport/transit/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});
        const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur');
        successMsg.value=d.message;await loadTransitDossiers();setTimeout(()=>successMsg.value='',3000);
    }catch(e){error.value=e.message}
};

// ═══════════════════════════════════════════════════════════════════
// Tab — Navigation par hash URL
// ═══════════════════════════════════════════════════════════════════
const hotelChambres = ref([]);
const hotelChambresLoading = ref(false); const showHotelChambreModal = ref(false); const hotelChambreSubmitting = ref(false);
const hotelChambreForm = ref({numero_chambre:'',type:'standard',categorie:'',prix_nuitee:0,capacite:2,etage:0,statut:'disponible',equipements:'',notes:''});
const loadHotelChambres = async () => { hotelChambresLoading.value=true; try{const r=await fetch('/api/company/accounting/hotel/chambres');if(r.ok)hotelChambres.value=await r.json();}catch(e){error.value=e.message}finally{hotelChambresLoading.value=false}};
const openHotelChambreModal = () => { hotelChambreForm.value={numero_chambre:'',type:'standard',categorie:'',prix_nuitee:0,capacite:2,etage:0,statut:'disponible',equipements:'',notes:''}; showHotelChambreModal.value=true;};
const submitHotelChambre = async () => { hotelChambreSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/hotel/chambres',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(hotelChambreForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showHotelChambreModal.value=false;await loadHotelChambres();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{hotelChambreSubmitting.value=false}};
const deleteHotelChambre = async (id) => { if(!confirm('Supprimer cette chambre ?'))return; try{const r=await fetch(`/api/company/accounting/hotel/chambres/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadHotelChambres();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const hotelReservations = ref([]);
const hotelReservationsLoading = ref(false); const showHotelReservationModal = ref(false); const hotelReservationSubmitting = ref(false);
const hotelReservationForm = ref({numero_reservation:'RES-',chambre_id:'',client_nom:'',client_contact:'',client_email:'',date_arrivee:'',date_depart:'',nb_nuitees:1,nb_adultes:1,nb_enfants:0,montant_total:0,acompte:0,statut:'confirmee',source:'',notes:''});
const loadHotelReservations = async () => { hotelReservationsLoading.value=true; try{const r=await fetch('/api/company/accounting/hotel/reservations');if(r.ok)hotelReservations.value=await r.json();}catch(e){error.value=e.message}finally{hotelReservationsLoading.value=false}};
const openHotelReservationModal = () => { hotelReservationForm.value={numero_reservation:'RES-'+Date.now(),chambre_id:'',client_nom:'',client_contact:'',client_email:'',date_arrivee:'',date_depart:'',nb_nuitees:1,nb_adultes:1,nb_enfants:0,montant_total:0,acompte:0,statut:'confirmee',source:'',notes:''}; showHotelReservationModal.value=true;};
const submitHotelReservation = async () => { hotelReservationSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/hotel/reservations',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(hotelReservationForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showHotelReservationModal.value=false;await loadHotelReservations();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{hotelReservationSubmitting.value=false}};
const deleteHotelReservation = async (id) => { if(!confirm('Supprimer cette reservation ?'))return; try{const r=await fetch(`/api/company/accounting/hotel/reservations/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadHotelReservations();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const scolaireEleves = ref([]);
const scolaireElevesLoading = ref(false); const showScolaireEleveModal = ref(false); const scolaireEleveSubmitting = ref(false);
const scolaireEleveForm = ref({matricule:'',nom:'',prenom:'',date_naissance:'',sexe:'M',classe:'',annee_scolaire:'',niveau:'',statut:'actif',nom_tuteur:'',contact_tuteur:'',email_tuteur:'',adresse:'',notes:''});
const loadScolaireEleves = async () => { scolaireElevesLoading.value=true; try{const r=await fetch('/api/company/accounting/scolaire/eleves');if(r.ok)scolaireEleves.value=await r.json();}catch(e){error.value=e.message}finally{scolaireElevesLoading.value=false}};
const openScolaireEleveModal = () => { scolaireEleveForm.value={matricule:'',nom:'',prenom:'',date_naissance:'',sexe:'M',classe:'',annee_scolaire:new Date().getFullYear()+'/'+(new Date().getFullYear()+1),niveau:'',statut:'actif',nom_tuteur:'',contact_tuteur:'',email_tuteur:'',adresse:'',notes:''}; showScolaireEleveModal.value=true;};
const submitScolaireEleve = async () => { scolaireEleveSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/scolaire/eleves',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(scolaireEleveForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showScolaireEleveModal.value=false;await loadScolaireEleves();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{scolaireEleveSubmitting.value=false}};
const deleteScolaireEleve = async (id) => { if(!confirm('Supprimer cet eleve ?'))return; try{const r=await fetch(`/api/company/accounting/scolaire/eleves/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadScolaireEleves();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const locationBiens = ref([]);
const locationBiensLoading = ref(false); const showLocationBienModal = ref(false); const locationBienSubmitting = ref(false);
const locationBienForm = ref({reference_bien:'',designation:'',type:'appartement',adresse:'',ville:'',quartier:'',surface:0,nb_pieces:1,loyer_mensuel:0,charges_mensuelles:0,caution:0,statut:'disponible',locataire_actuel:'',date_debut_bail:'',date_fin_bail:'',notes:''});
const loadLocationBiens = async () => { locationBiensLoading.value=true; try{const r=await fetch('/api/company/accounting/location/biens');if(r.ok)locationBiens.value=await r.json();}catch(e){error.value=e.message}finally{locationBiensLoading.value=false}};
const openLocationBienModal = () => { locationBienForm.value={reference_bien:'B-'+Date.now(),designation:'',type:'appartement',adresse:'',ville:'',quartier:'',surface:0,nb_pieces:1,loyer_mensuel:0,charges_mensuelles:0,caution:0,statut:'disponible',locataire_actuel:'',date_debut_bail:'',date_fin_bail:'',notes:''}; showLocationBienModal.value=true;};
const submitLocationBien = async () => { locationBienSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/location/biens',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(locationBienForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showLocationBienModal.value=false;await loadLocationBiens();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{locationBienSubmitting.value=false}};
const deleteLocationBien = async (id) => { if(!confirm('Supprimer ce bien ?'))return; try{const r=await fetch(`/api/company/accounting/location/biens/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadLocationBiens();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const tontines = ref([]);
const tontinesLoading = ref(false); const showTontineModal = ref(false); const tontineSubmitting = ref(false);
const tontineForm = ref({nom_groupe:'',description:'',nb_membres:5,montant_cotisation:0,frequence:'mensuelle',montant_caisse:0,date_creation:'',statut:'active',regles:''});
const loadTontines = async () => { tontinesLoading.value=true; try{const r=await fetch('/api/company/accounting/tontine/groupes');if(r.ok)tontines.value=await r.json();}catch(e){error.value=e.message}finally{tontinesLoading.value=false}};
const openTontineModal = () => { tontineForm.value={nom_groupe:'',description:'',nb_membres:5,montant_cotisation:0,frequence:'mensuelle',montant_caisse:0,date_creation:new Date().toISOString().split('T')[0],statut:'active',regles:''}; showTontineModal.value=true;};
const submitTontine = async () => { tontineSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/tontine/groupes',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(tontineForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showTontineModal.value=false;await loadTontines();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{tontineSubmitting.value=false}};
const deleteTontine = async (id) => { if(!confirm('Supprimer cette tontine ?'))return; try{const r=await fetch(`/api/company/accounting/tontine/groupes/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadTontines();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const pressingCommandes = ref([]);
const pressingCommandesLoading = ref(false); const showPressingCommandeModal = ref(false); const pressingCommandeSubmitting = ref(false);
const pressingCommandeForm = ref({numero_commande:'',client_nom:'',client_contact:'',date_depot:'',date_retrait_prevu:'',nb_articles:1,articles:'',type_service:'nettoyage',montant_total:0,acompte:0,statut:'en_cours',notes:''});
const loadPressingCommandes = async () => { pressingCommandesLoading.value=true; try{const r=await fetch('/api/company/accounting/pressing/commandes');if(r.ok)pressingCommandes.value=await r.json();}catch(e){error.value=e.message}finally{pressingCommandesLoading.value=false}};
const openPressingCommandeModal = () => { pressingCommandeForm.value={numero_commande:'PC-'+Date.now(),client_nom:'',client_contact:'',date_depot:new Date().toISOString().split('T')[0],date_retrait_prevu:'',nb_articles:1,articles:'',type_service:'nettoyage',montant_total:0,acompte:0,statut:'en_cours',notes:''}; showPressingCommandeModal.value=true;};
const submitPressingCommande = async () => { pressingCommandeSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/pressing/commandes',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(pressingCommandeForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showPressingCommandeModal.value=false;await loadPressingCommandes();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{pressingCommandeSubmitting.value=false}};
const deletePressingCommande = async (id) => { if(!confirm('Supprimer cette commande ?'))return; try{const r=await fetch(`/api/company/accounting/pressing/commandes/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadPressingCommandes();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const morgueDepots = ref([]);
const morgueDepotsLoading = ref(false); const showMorgueDepotModal = ref(false); const morgueDepotSubmitting = ref(false);
const morgueDepotForm = ref({numero_dossier:'',defunt_nom:'',defunt_prenom:'',date_deces:'',date_depot:'',date_sortie:'',famille_contact:'',famille_nom:'',type_conservation:'normale',nb_jours:1,tarif_journalier:0,montant_total:0,montant_paye:0,statut:'en_cours',notes:''});
const loadMorgueDepots = async () => { morgueDepotsLoading.value=true; try{const r=await fetch('/api/company/accounting/morgue/depots');if(r.ok)morgueDepots.value=await r.json();}catch(e){error.value=e.message}finally{morgueDepotsLoading.value=false}};
const openMorgueDepotModal = () => { morgueDepotForm.value={numero_dossier:'DEP-'+Date.now(),defunt_nom:'',defunt_prenom:'',date_deces:'',date_depot:new Date().toISOString().split('T')[0],date_sortie:'',famille_contact:'',famille_nom:'',type_conservation:'normale',nb_jours:1,tarif_journalier:0,montant_total:0,montant_paye:0,statut:'en_cours',notes:''}; showMorgueDepotModal.value=true;};
const submitMorgueDepot = async () => { morgueDepotSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/morgue/depots',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(morgueDepotForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showMorgueDepotModal.value=false;await loadMorgueDepots();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{morgueDepotSubmitting.value=false}};
const deleteMorgueDepot = async (id) => { if(!confirm('Supprimer ce depot ?'))return; try{const r=await fetch(`/api/company/accounting/morgue/depots/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadMorgueDepots();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const morgueFactures = ref([]);
const morgueFacturesLoading = ref(false); const showMorgueFactureModal = ref(false); const morgueFactureSubmitting = ref(false);
const morgueFactureForm = ref({numero_facture:'',depot_id:'',client_nom:'',defunt_nom:'',type_prestation:'conservation',nb_jours:0,montant_ht:0,tva:0,montant_ttc:0,montant_paye:0,statut:'en_attente',notes:''});
const loadMorgueFactures = async () => { morgueFacturesLoading.value=true; try{const r=await fetch('/api/company/accounting/morgue/factures');if(r.ok)morgueFactures.value=await r.json();}catch(e){error.value=e.message}finally{morgueFacturesLoading.value=false}};
const openMorgueFactureModal = () => { morgueFactureForm.value={numero_facture:'FACT-M-'+Date.now(),depot_id:'',client_nom:'',defunt_nom:'',type_prestation:'conservation',nb_jours:0,montant_ht:0,tva:0,montant_ttc:0,montant_paye:0,statut:'en_attente',notes:''}; showMorgueFactureModal.value=true;};
const submitMorgueFacture = async () => { morgueFactureSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/morgue/factures',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(morgueFactureForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showMorgueFactureModal.value=false;await loadMorgueFactures();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{morgueFactureSubmitting.value=false}};
const deleteMorgueFacture = async (id) => { if(!confirm('Supprimer cette facture ?'))return; try{const r=await fetch(`/api/company/accounting/morgue/factures/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadMorgueFactures();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const grillesTarifaires = ref([]);
const grillesTarifairesLoading = ref(false); const showGrilleTarifaireModal = ref(false); const grilleTarifaireSubmitting = ref(false);
const grilleTarifaireForm = ref({code:'',designation:'',categorie:'',unite:'',prix_unitaire:0,tva:0,remise_max:0,date_validite_debut:'',date_validite_fin:'',notes:''});
const loadGrillesTarifaires = async () => { grillesTarifairesLoading.value=true; try{const r=await fetch('/api/company/accounting/grilles-tarifaires');if(r.ok)grillesTarifaires.value=await r.json();}catch(e){error.value=e.message}finally{grillesTarifairesLoading.value=false}};
const openGrilleTarifaireModal = () => { grilleTarifaireForm.value={code:'',designation:'',categorie:'',unite:'',prix_unitaire:0,tva:0,remise_max:0,date_validite_debut:'',date_validite_fin:'',notes:''}; showGrilleTarifaireModal.value=true;};
const submitGrilleTarifaire = async () => { grilleTarifaireSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/grilles-tarifaires',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(grilleTarifaireForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showGrilleTarifaireModal.value=false;await loadGrillesTarifaires();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{grilleTarifaireSubmitting.value=false}};
const deleteGrilleTarifaire = async (id) => { if(!confirm('Supprimer cette grille ?'))return; try{const r=await fetch(`/api/company/accounting/grilles-tarifaires/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadGrillesTarifaires();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const commissions = ref([]);
const commissionsLoading = ref(false); const showCommissionModal = ref(false); const commissionSubmitting = ref(false);
const commissionForm = ref({numero_commission:'',type:'vente',agent_nom:'',agent_contact:'',montant_base:0,taux_commission:0,montant_commission:0,tva:0,montant_net:0,montant_paye:0,date_operation:'',date_paiement:'',statut:'calculee',description:''});
const loadCommissions = async () => { commissionsLoading.value=true; try{const r=await fetch('/api/company/accounting/commissions');if(r.ok)commissions.value=await r.json();}catch(e){error.value=e.message}finally{commissionsLoading.value=false}};
const openCommissionModal = () => { commissionForm.value={numero_commission:'COM-'+Date.now(),type:'vente',agent_nom:'',agent_contact:'',montant_base:0,taux_commission:0,montant_commission:0,tva:0,montant_net:0,montant_paye:0,date_operation:new Date().toISOString().split('T')[0],date_paiement:'',statut:'calculee',description:''}; showCommissionModal.value=true;};
const submitCommission = async () => { commissionSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/commissions',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(commissionForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showCommissionModal.value=false;await loadCommissions();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{commissionSubmitting.value=false}};
const deleteCommission = async (id) => { if(!confirm('Supprimer cette commission ?'))return; try{const r=await fetch(`/api/company/accounting/commissions/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadCommissions();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const mobileTransactions = ref([]);
const mobileTransactionsLoading = ref(false); const showMobileTransactionModal = ref(false); const mobileTransactionSubmitting = ref(false);
const mobileTransactionForm = ref({reference_transaction:'',operateur:'mtn',type:'depot',numero_expediteur:'',numero_destinataire:'',nom_expediteur:'',nom_destinataire:'',montant:0,frais:0,solde_avant:0,solde_apres:0,date_transaction:'',statut:'effectuee',motif:''});
const loadMobileTransactions = async () => { mobileTransactionsLoading.value=true; try{const r=await fetch('/api/company/accounting/mobile-money');if(r.ok)mobileTransactions.value=await r.json();}catch(e){error.value=e.message}finally{mobileTransactionsLoading.value=false}};
const openMobileTransactionModal = () => { mobileTransactionForm.value={reference_transaction:'MOB-'+Date.now(),operateur:'mtn',type:'depot',numero_expediteur:'',numero_destinataire:'',nom_expediteur:'',nom_destinataire:'',montant:0,frais:0,solde_avant:0,solde_apres:0,date_transaction:new Date().toISOString().slice(0,16),statut:'effectuee',motif:''}; showMobileTransactionModal.value=true;};
const submitMobileTransaction = async () => { mobileTransactionSubmitting.value=true;error.value=null; try{const r=await fetch('/api/company/accounting/mobile-money',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'},body:JSON.stringify(mobileTransactionForm.value)});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;showMobileTransactionModal.value=false;await loadMobileTransactions();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}finally{mobileTransactionSubmitting.value=false}};
const deleteMobileTransaction = async (id) => { if(!confirm('Supprimer cette transaction ?'))return; try{const r=await fetch(`/api/company/accounting/mobile-money/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken.value,Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'Erreur'); successMsg.value=d.message;await loadMobileTransactions();setTimeout(()=>successMsg.value='',3000);}catch(e){error.value=e.message}};

const invoicesList = ref([]);
const invoicesLoading = ref(false);
const loadInvoices = async () => { invoicesLoading.value=true; try{const r=await fetch('/api/company/accounting/invoices');if(r.ok)invoicesList.value=await r.json();}catch(e){error.value=e.message}finally{invoicesLoading.value=false}};
const taxeNuiteesList = ref([]);
const taxeNuiteesLoading = ref(false);
const loadTaxeNuitees = async () => { taxeNuiteesLoading.value=true; try{const r=await fetch('/api/company/accounting/taxe-nuitees');if(r.ok)taxeNuiteesList.value=await r.json();}catch(e){error.value=e.message}finally{taxeNuiteesLoading.value=false}};
const loyersImpayesList = ref([]);
const loyersImpayesLoading = ref(false);
const loadLoyersImpayes = async () => { loyersImpayesLoading.value=true; try{const r=await fetch('/api/company/accounting/loyers-impayes');if(r.ok)loyersImpayesList.value=await r.json();}catch(e){error.value=e.message}finally{loyersImpayesLoading.value=false}};
const treasuryData = ref(null);
const treasuryLoading = ref(false);
const loadTreasury = async () => { treasuryLoading.value=true; try{const r=await fetch('/api/company/accounting/treasury');if(r.ok)treasuryData.value=await r.json();}catch(e){error.value=e.message}finally{treasuryLoading.value=false}};

const onTabChange = (tab) => {
    activeTab.value=tab;error.value=null;
    // Mettre à jour le hash dans l'URL
    if (window.location.hash !== '#'+tab) {
        history.replaceState(null, '', '#stock');
        // Correction: il faut modifier le hash après le display
    }
    window.location.hash = tab;
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
    if(tab==='stock'&&!stockItems.value.length)loadStockItems();
    if((tab==='arrivages'||tab==='commandes')&&!movements.value.length)loadMovements();
    if(tab==='emballages'&&!emballages.value.length)loadEmballages();
    if(tab==='hotel-factures'&&!hotelFactures.value.length)loadHotelFactures();
    if(tab==='scolarite-factures'&&!scolaireFactures.value.length)loadScolaireFactures();
    if(tab==='quittances'&&!quittances.value.length)loadQuittances();
    if(tab==='cotisations'&&!cotisations.value.length)loadCotisations();
    if(tab==='transit-dossiers'&&!transitDossiers.value.length)loadTransitDossiers();
    if(tab==='hotel-chambres'&&!hotelChambres.value.length)loadHotelChambres();
    if(tab==='hotel-reservations'&&!hotelReservations.value.length)loadHotelReservations();
    if(tab==='invoices'&&!invoicesList.value.length)loadInvoices();
    if(tab==='taxe-nuitee'&&!taxeNuiteesList.value.length)loadTaxeNuitees();
    if(tab==='scolarite-eleves'&&!scolaireEleves.value.length)loadScolaireEleves();
    if(tab==='biens-location'&&!locationBiens.value.length)loadLocationBiens();
    if(tab==='loyers-impayes'&&!loyersImpayesList.value.length)loadLoyersImpayes();
    if(tab==='tontines'&&!tontines.value.length)loadTontines();
    if(tab==='pressing-commandes'&&!pressingCommandes.value.length)loadPressingCommandes();
    if(tab==='morgue-depots'&&!morgueDepots.value.length)loadMorgueDepots();
    if(tab==='morgue-factures'&&!morgueFactures.value.length)loadMorgueFactures();
    if(tab==='treasury'&&!treasuryData.value)loadTreasury();
    if(tab==='grilles-tarifaires'&&!grillesTarifaires.value.length)loadGrillesTarifaires();
    if(tab==='commissions'&&!commissions.value.length)loadCommissions();
    if(tab==='mobile-money'&&!mobileTransactions.value.length)loadMobileTransactions();
};

onMounted(() => {
    loadStats();loadAccounts();loadJournals();
    // Lire le hash URL pour ouvrir l'onglet correspondant
    const hash = window.location.hash.replace('#','');
    if (hash && tabs.value.some(t => t.key === hash)) {
        activeTab.value = hash;
        onTabChange(hash);
    }
});
</script>

<template>
    <CompanyLayout page-title="Comptabilité">
        <div v-if="successMsg" class="isup-alert-success mb-2">{{ successMsg }}</div>
        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>

        <div class="isup-shell isup-shell--no-header">
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

                <!-- ==================== SECTIONS MÉTIER PAR DOMAINE ==================== -->

                <!-- Facturation -->
                <div v-if="activeTab==='invoices'">
                    <div class="isup-panel">
                        <div class="isup-panel-header"><span><i class="bi-receipt-cutoff me-2" style="color:#FF7900;"></i>Facturation</span></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="invoicesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!invoicesList.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-receipt-cutoff" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune facture.</p>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Facture</th><th>Client</th><th>Date</th><th class="text-end">Montant</th><th class="text-end">Payé</th><th class="text-end">Solde</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="f in invoicesList" :key="f.id">
                                        <td class="ps-4 fw-semibold">{{ f.numero||f.reference||'N/A' }}</td>
                                        <td>{{ f.client_nom||f.client_name||'-' }}</td>
                                        <td>{{ f.date_facture||f.date||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(f.montant_ttc||f.total_ttc||f.montant||0) }}</td>
                                        <td class="text-end">{{ formatCurrency(f.montant_paye||f.paye||0) }}</td>
                                        <td class="text-end fw-semibold" :style="{color:(f.solde||(f.montant_ttc-f.montant_paye))>0?'#c62828':'#2e7d32'}">{{ formatCurrency(f.solde||(f.montant_ttc-f.montant_paye)) }}</td>
                                        <td><span class="isup-status" :class="f.statut==='payee'||f.statut==='payé'?'isup-status-green':f.statut==='partielle'?'isup-status-blue':'isup-status-grey'">{{ f.statut||'en_attente' }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteInvoice?deleteInvoice(f.id):null"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock -->
                <div v-if="activeTab==='stock'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-box-seam me-2" style="color:#FF7900;"></i>Gestion de stock</span>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="small" style="color:#888;">Valo. totale: <strong>{{ formatCurrency(totalStockValue) }}</strong></span>
                                <button class="isup-btn-grey" @click="showImportStock=!showImportStock"><i class="bi-upload me-1"></i>Import CSV</button>
                                <button class="isup-btn-primary" @click="openStockModal()"><i class="bi-plus-lg me-1"></i>Nouvel article</button>
                            </div>
                        </div>
                        <div v-if="showImportStock" class="isup-panel-body" style="border-bottom:1px solid #f0f4f8;">
                            <p class="small mb-2" style="color:#888;">Fichier CSV avec colonnes: <code>reference, designation, purchase_price, selling_price, stock_alert, unit</code></p>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="file" class="isup-input" style="max-width:300px;" accept=".csv" @change="e=>importStockFile=e.target.files[0]">
                                <button class="isup-btn-primary" @click="importStockItems"><i class="bi-upload me-1"></i>Importer</button>
                            </div>
                            <div v-if="importStockError" style="color:#c62828;font-size:11px;margin-top:4px;">{{ importStockError }}</div>
                            <div v-if="importStockSuccess" style="color:#2e7d32;font-size:11px;margin-top:4px;">{{ importStockSuccess }}</div>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="stockLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!stockItems.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-box-seam" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucun article en stock.</p>
                                <button class="isup-btn-primary" @click="openStockModal()">Créer un article</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Réf.</th><th>Désignation</th><th>Catégorie</th><th class="text-end">Stock</th><th class="text-end">Seuil</th><th class="text-end">PA</th><th class="text-end">PV</th><th class="text-end">Valo.</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody>
                                        <tr v-for="i in stockItems" :key="i.id">
                                            <td class="ps-4 fw-semibold">{{ i.reference }}</td>
                                            <td>{{ i.designation }}</td>
                                            <td><span class="isup-badge isup-badge-light">{{ i.category||'-' }}</span></td>
                                            <td class="text-end fw-semibold" :style="{color:i.stock<=i.stock_alert?'#c62828':'#2e7d32'}">{{ i.stock }} {{ i.unit }}</td>
                                            <td class="text-end">{{ i.stock_alert }}</td>
                                            <td class="text-end">{{ formatCurrency(i.purchase_price) }}</td>
                                            <td class="text-end">{{ formatCurrency(i.selling_price) }}</td>
                                            <td class="text-end fw-semibold">{{ formatCurrency(i.stock*i.purchase_price) }}</td>
                                            <td class="text-end pe-4">
                                                <button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteStockItem(i.id)"><i class="bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot style="background:#EEF3F9;font-weight:700;">
                                        <tr><td colspan="4" class="ps-4">Totaux: {{ stockItems.length }} articles</td><td class="text-end">{{ stockItems.reduce((s,i)=>s+i.stock,0) }} unités</td><td></td><td></td><td class="text-end">{{ formatCurrency(totalStockValue) }}</td><td></td></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arrivages -->
                <div v-if="activeTab==='arrivages'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-truck me-2" style="color:#FF7900;"></i>Arrivages (entrées stock)</span>
                            <button v-if="stockItems.length" class="isup-btn-primary" @click="showMovementModal='entry'"><i class="bi-plus-lg me-1"></i>Nouvelle entrée</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="!stockItems.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-truck" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Ajoutez d'abord des articles en stock.</p>
                                <button class="isup-btn-primary" @click="onTabChange('stock')">Aller au stock</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100"><thead><tr><th class="ps-4">Date</th><th>Article</th><th>Réf.</th><th class="text-end">Qté entrée</th><th>Motif</th></tr></thead>
                                    <tbody><tr v-for="m in entries" :key="m.id"><td class="ps-4">{{ m.date }}</td><td class="fw-semibold">{{ m.item }}</td><td>{{ m.item_ref }}</td><td class="text-end fw-semibold" style="color:#2e7d32;">+{{ m.quantity }}</td><td>{{ m.motif||'-' }}</td></tr>
                                        <tr v-if="!entries.length"><td colspan="5" class="text-center py-4 isup-empty-cell">Aucune entrée enregistrée.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commandes (sorties stock) -->
                <div v-if="activeTab==='commandes'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-cart3 me-2" style="color:#FF7900;"></i>Commandes (sorties stock)</span>
                            <button v-if="stockItems.length" class="isup-btn-primary" @click="openMovementModal('exit')"><i class="bi-plus-lg me-1"></i>Nouvelle sortie</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="!stockItems.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-cart3" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Ajoutez d'abord des articles en stock.</p>
                                <button class="isup-btn-primary" @click="onTabChange('stock')">Aller au stock</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100"><thead><tr><th class="ps-4">Date</th><th>Article</th><th>Réf.</th><th class="text-end">Qté sortie</th><th>Motif</th></tr></thead>
                                    <tbody><tr v-for="m in exits" :key="m.id"><td class="ps-4">{{ m.date }}</td><td class="fw-semibold">{{ m.item }}</td><td>{{ m.item_ref }}</td><td class="text-end fw-semibold" style="color:#c62828;">-{{ m.quantity }}</td><td>{{ m.motif||'-' }}</td></tr>
                                        <tr v-if="!exits.length"><td colspan="5" class="text-center py-4 isup-empty-cell">Aucune sortie enregistrée.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emballages consignés -->
                <div v-if="activeTab==='emballages'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-box me-2" style="color:#FF7900;"></i>Emballages consignés</span>
                            <button class="isup-btn-primary" @click="openEmballageModal()"><i class="bi-plus-lg me-1"></i>Nouvel emballage</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="emballagesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!emballages.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-box" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucun emballage consigné.</p>
                                <button class="isup-btn-primary" @click="openEmballageModal()">Nouvel emballage</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Type</th><th>Tiers</th><th>Produit</th><th class="text-end">Qté</th><th class="text-end">Consigne</th><th>Émission</th><th>Retour</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="e in emballages" :key="e.id">
                                        <td class="ps-4"><span class="isup-badge isup-badge-light">{{ e.type }}</span></td>
                                        <td>{{ e.tiers_nom||'-' }} <small v-if="e.tiers_type" style="color:#888;">({{ e.tiers_type }})</small></td>
                                        <td>{{ e.produit||'-' }}</td>
                                        <td class="text-end fw-semibold">{{ e.quantite }}</td>
                                        <td class="text-end">{{ formatCurrency(e.montant_consigne) }}</td>
                                        <td>{{ e.date_emission||'-' }}</td>
                                        <td>{{ e.date_retour||'-' }}</td>
                                        <td><span class="isup-status" :class="e.statut==='en_cours'?'isup-status-blue':'isup-status-green'">{{ e.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteEmballage(e.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hôtel - Chambres -->
                <div v-if="activeTab==='hotel-chambres'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-door-open me-2" style="color:#FF7900;"></i>Gestion des chambres</span>
                            <button class="isup-btn-primary" @click="openHotelChambreModal()"><i class="bi-plus-lg me-1"></i>Nouvelle chambre</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="hotelChambresLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!hotelChambres.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-door-open" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune chambre.</p>
                                <button class="isup-btn-primary" @click="openHotelChambreModal()">Créer une chambre</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Chambre</th><th>Type</th><th>Catégorie</th><th class="text-end">Prix/Nuitée</th><th class="text-end">Capacité</th><th>Étage</th><th>Statut</th><th>Équipements</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="c in hotelChambres" :key="c.id">
                                        <td class="ps-4 fw-semibold">{{ c.numero_chambre }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ c.type }}</span></td>
                                        <td>{{ c.categorie||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(c.prix_nuitee) }}</td>
                                        <td class="text-end">{{ c.capacite }} pers.</td>
                                        <td>{{ c.etage||'RDC' }}</td>
                                        <td><span class="isup-status" :class="c.statut==='disponible'?'isup-status-green':'isup-status-grey'">{{ c.statut }}</span></td>
                                        <td>{{ c.equipements||'-' }}</td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteHotelChambre(c.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hôtel - Réservations -->
                <div v-if="activeTab==='hotel-reservations'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-calendar-check me-2" style="color:#FF7900;"></i>Réservations</span>
                            <button class="isup-btn-primary" @click="openHotelReservationModal()"><i class="bi-plus-lg me-1"></i>Nouvelle réservation</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="hotelReservationsLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!hotelReservations.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-calendar-check" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune réservation.</p>
                                <button class="isup-btn-primary" @click="openHotelReservationModal()">Créer une réservation</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Réservation</th><th>Client</th><th>Arrivée</th><th>Départ</th><th class="text-end">Nuitées</th><th class="text-end">Total</th><th class="text-end">Acompte</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="r in hotelReservations" :key="r.id">
                                        <td class="ps-4 fw-semibold">{{ r.numero_reservation }}</td>
                                        <td>{{ r.client_nom||'-' }}</td>
                                        <td>{{ r.date_arrivee||'-' }}</td>
                                        <td>{{ r.date_depart||'-' }}</td>
                                        <td class="text-end">{{ r.nb_nuitees }}</td>
                                        <td class="text-end">{{ formatCurrency(r.montant_total) }}</td>
                                        <td class="text-end">{{ formatCurrency(r.acompte) }}</td>
                                        <td><span class="isup-status" :class="r.statut==='confirmee'||r.statut==='confirmée'?'isup-status-green':r.statut==='en_attente'?'isup-status-grey':'isup-status-blue'">{{ r.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteHotelReservation(r.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hôtel - Factures hôtel -->
                <div v-if="activeTab==='hotel-factures'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-receipt me-2" style="color:#FF7900;"></i>Facturation hôtelière</span>
                            <button class="isup-btn-primary" @click="openHotelFactureModal()"><i class="bi-plus-lg me-1"></i>Nouvelle facture</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="hotelFacturesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!hotelFactures.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-receipt" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune facture hôtelière.</p>
                                <button class="isup-btn-primary" @click="openHotelFactureModal()">Créer une facture</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Facture</th><th>Type</th><th>Client</th><th>Chambre</th><th>Arrivée</th><th>Départ</th><th>Nuitées</th><th class="text-end">TTC</th><th class="text-end">Payé</th><th class="text-end">Solde</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="f in hotelFactures" :key="f.id">
                                        <td class="ps-4 fw-semibold">{{ f.numero_facture }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ f.type }}</span></td>
                                        <td>{{ f.client_nom||'-' }}</td>
                                        <td>{{ f.chambre||'-' }}</td>
                                        <td>{{ f.date_arrivee||'-' }}</td>
                                        <td>{{ f.date_depart||'-' }}</td>
                                        <td class="text-end">{{ f.nb_nuitees||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(f.montant_ttc) }}</td>
                                        <td class="text-end">{{ formatCurrency(f.montant_paye) }}</td>
                                        <td class="text-end fw-semibold" :style="{color:f.solde>0?'#c62828':'#2e7d32'}">{{ formatCurrency(f.solde) }}</td>
                                        <td><span class="isup-status" :class="f.statut==='payee'?'isup-status-green':f.statut==='partielle'?'isup-status-blue':'isup-status-grey'">{{ f.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteHotelFacture(f.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Taxe de séjour -->
                <div v-if="activeTab==='taxe-nuitee'">
                    <div class="isup-panel">
                        <div class="isup-panel-header"><span><i class="bi-cash-stack me-2" style="color:#FF7900;"></i>Taxe de séjour / Nuitée</span></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="taxeNuiteesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!taxeNuiteesList.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-cash-stack" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune déclaration de taxe de séjour.</p>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Période</th><th>Client</th><th>Chambre</th><th class="text-end">Nuitées</th><th class="text-end">Base</th><th class="text-end">Taux</th><th class="text-end">Montant</th><th class="text-end pe-4">Payé</th></tr></thead>
                                    <tbody><tr v-for="t in taxeNuiteesList" :key="t.id">
                                        <td class="ps-4">{{ t.periode||t.mois||'-' }}</td>
                                        <td>{{ t.client_nom||'-' }}</td>
                                        <td>{{ t.chambre||'-' }}</td>
                                        <td class="text-end">{{ t.nb_nuitees||0 }}</td>
                                        <td class="text-end">{{ formatCurrency(t.base||t.montant_base||0) }}</td>
                                        <td class="text-end">{{ t.taux||'10%' }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(t.montant||0) }}</td>
                                        <td class="text-end pe-4">{{ formatCurrency(t.paye||0) }}</td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scolaire - Factures -->
                <div v-if="activeTab==='scolarite-factures'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-mortarboard me-2" style="color:#FF7900;"></i>Facturation scolarité</span>
                            <button class="isup-btn-primary" @click="openScolaireFactureModal()"><i class="bi-plus-lg me-1"></i>Nouvelle facture</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="scolaireFacturesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!scolaireFactures.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-mortarboard" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune facture scolaire.</p>
                                <button class="isup-btn-primary" @click="openScolaireFactureModal()">Créer une facture</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N°</th><th>Année</th><th>Élève</th><th>Classe</th><th>Frais</th><th>Période</th><th class="text-end">Montant</th><th class="text-end">Payé</th><th class="text-end">Solde</th><th>Échéance</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="f in scolaireFactures" :key="f.id">
                                        <td class="ps-4 fw-semibold">{{ f.numero_facture }}</td>
                                        <td>{{ f.annee_scolaire }}</td>
                                        <td>{{ f.eleve_nom }} {{ f.eleve_prenom||'' }}</td>
                                        <td>{{ f.classe }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ f.type_frais }}</span></td>
                                        <td>{{ f.periode||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(f.montant_net) }}</td>
                                        <td class="text-end">{{ formatCurrency(f.montant_paye) }}</td>
                                        <td class="text-end fw-semibold" :style="{color:f.solde>0?'#c62828':'#2e7d32'}">{{ formatCurrency(f.solde) }}</td>
                                        <td>{{ f.date_echeance||'-' }}</td>
                                        <td><span class="isup-status" :class="f.statut==='payee'?'isup-status-green':f.statut==='partielle'?'isup-status-blue':'isup-status-grey'">{{ f.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteScolaireFacture(f.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scolaire - Élèves -->
                <div v-if="activeTab==='scolarite-eleves'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-people me-2" style="color:#FF7900;"></i>Gestion des élèves</span>
                            <button class="isup-btn-primary" @click="openScolaireEleveModal()"><i class="bi-plus-lg me-1"></i>Nouvel élève</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="scolaireElevesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!scolaireEleves.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-people" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucun élève inscrit.</p>
                                <button class="isup-btn-primary" @click="openScolaireEleveModal()">Inscrire un élève</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Matricule</th><th>Nom</th><th>Prénom</th><th>Classe</th><th>Niveau</th><th>Année</th><th>Statut</th><th>Tuteur</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="e in scolaireEleves" :key="e.id">
                                        <td class="ps-4 fw-semibold">{{ e.matricule }}</td>
                                        <td>{{ e.nom }}</td>
                                        <td>{{ e.prenom }}</td>
                                        <td>{{ e.classe }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ e.niveau||'-' }}</span></td>
                                        <td>{{ e.annee_scolaire }}</td>
                                        <td><span class="isup-status" :class="e.statut==='actif'?'isup-status-green':'isup-status-grey'">{{ e.statut }}</span></td>
                                        <td>{{ e.nom_tuteur||'-' }}</td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteScolaireEleve(e.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location - Quittances -->
                <div v-if="activeTab==='quittances'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-file-text me-2" style="color:#FF7900;"></i>Quittances de loyer</span>
                            <button class="isup-btn-primary" @click="openQuittanceModal()"><i class="bi-plus-lg me-1"></i>Nouvelle quittance</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="quittancesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!quittances.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-file-text" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune quittance.</p>
                                <button class="isup-btn-primary" @click="openQuittanceModal()">Créer une quittance</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Quittance</th><th>Bien</th><th>Locataire</th><th>Période</th><th>Début</th><th>Fin</th><th class="text-end">Loyer HT</th><th class="text-end">Charges</th><th class="text-end">Total</th><th class="text-end">Payé</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="q in quittances" :key="q.id">
                                        <td class="ps-4 fw-semibold">{{ q.numero_quittance }}</td>
                                        <td>{{ q.bien||'-' }}</td>
                                        <td>{{ q.locataire_nom }}</td>
                                        <td>{{ q.periode }}</td>
                                        <td>{{ q.date_debut||'-' }}</td>
                                        <td>{{ q.date_fin||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(q.loyer_ht) }}</td>
                                        <td class="text-end">{{ formatCurrency(q.charges) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(q.montant_total) }}</td>
                                        <td class="text-end">{{ formatCurrency(q.montant_paye) }}</td>
                                        <td><span class="isup-status" :class="q.statut==='payee'?'isup-status-green':q.statut==='partielle'?'isup-status-blue':'isup-status-grey'">{{ q.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteQuittance(q.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location - Biens -->
                <div v-if="activeTab==='biens-location'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-building me-2" style="color:#FF7900;"></i>Biens en location</span>
                            <button class="isup-btn-primary" @click="openLocationBienModal()"><i class="bi-plus-lg me-1"></i>Nouveau bien</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="locationBiensLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!locationBiens.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-building" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucun bien en location.</p>
                                <button class="isup-btn-primary" @click="openLocationBienModal()">Ajouter un bien</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Réf.</th><th>Désignation</th><th>Type</th><th>Ville</th><th class="text-end">Loyer</th><th class="text-end">Charges</th><th>Statut</th><th>Locataire</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="b in locationBiens" :key="b.id">
                                        <td class="ps-4 fw-semibold">{{ b.reference_bien }}</td>
                                        <td>{{ b.designation }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ b.type }}</span></td>
                                        <td>{{ b.ville||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(b.loyer_mensuel) }}</td>
                                        <td class="text-end">{{ formatCurrency(b.charges_mensuelles) }}</td>
                                        <td><span class="isup-status" :class="b.statut==='disponible'?'isup-status-green':b.statut==='loue'?'isup-status-blue':'isup-status-grey'">{{ b.statut }}</span></td>
                                        <td>{{ b.locataire_actuel||'-' }}</td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteLocationBien(b.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location - Loyers impayés -->
                <div v-if="activeTab==='loyers-impayes'">
                    <div class="isup-panel">
                        <div class="isup-panel-header"><span><i class="bi-exclamation-triangle me-2" style="color:#FF7900;"></i>Loyers impayés</span></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="loyersImpayesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!loyersImpayesList.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-exclamation-triangle" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucun loyer impayé.</p>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Locataire</th><th>Bien</th><th>Période</th><th class="text-end">Loyer</th><th class="text-end">Retard</th><th class="text-end">Total dû</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="l in loyersImpayesList" :key="l.id">
                                        <td class="ps-4">{{ l.locataire_nom||l.client_nom||'-' }}</td>
                                        <td>{{ l.bien||l.designation||'-' }}</td>
                                        <td>{{ l.periode||l.mois||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(l.loyer_mensuel||l.montant||0) }}</td>
                                        <td class="text-end">{{ l.jours_retard||'N/A' }} jours</td>
                                        <td class="text-end fw-semibold" style="color:#c62828;">{{ formatCurrency(l.total_du||l.solde||0) }}</td>
                                        <td><span class="isup-status isup-status-grey">{{ l.statut||'impaye' }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-btn-grey btn-sm">Relancer</button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tontines -->
                <div v-if="activeTab==='tontines'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-people me-2" style="color:#FF7900;"></i>Gestion des tontines</span>
                            <button class="isup-btn-primary" @click="openTontineModal()"><i class="bi-plus-lg me-1"></i>Nouveau groupe</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="tontinesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!tontines.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-people" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucun groupe de tontine.</p>
                                <button class="isup-btn-primary" @click="openTontineModal()">Créer un groupe</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Nom</th><th class="text-end">Membres</th><th class="text-end">Cotisation</th><th>Fréquence</th><th class="text-end">Caisse</th><th>Création</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="g in tontines" :key="g.id">
                                        <td class="ps-4 fw-semibold">{{ g.nom_groupe }}</td>
                                        <td class="text-end">{{ g.nb_membres }}</td>
                                        <td class="text-end">{{ formatCurrency(g.montant_cotisation) }}</td>
                                        <td>{{ g.frequence }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(g.montant_caisse) }}</td>
                                        <td>{{ g.date_creation||'-' }}</td>
                                        <td><span class="isup-status" :class="g.statut==='active'?'isup-status-green':'isup-status-grey'">{{ g.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteTontine(g.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cotisations -->
                <div v-if="activeTab==='cotisations'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-coin me-2" style="color:#FF7900;"></i>Cotisations</span>
                            <button class="isup-btn-primary" @click="openCotisationModal()"><i class="bi-plus-lg me-1"></i>Nouvelle cotisation</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="cotisationsLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!cotisations.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-coin" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune cotisation.</p>
                                <button class="isup-btn-primary" @click="openCotisationModal()">Créer une cotisation</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Tontine</th><th>Membre</th><th>Période</th><th>Échéance</th><th class="text-end">Montant</th><th class="text-end">Payé</th><th class="text-end">Solde</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="c in cotisations" :key="c.id">
                                        <td class="ps-4 fw-semibold">{{ c.tontine_nom }}</td>
                                        <td>{{ c.membre_nom }}</td>
                                        <td>{{ c.periode }}</td>
                                        <td>{{ c.date_echeance||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(c.montant) }}</td>
                                        <td class="text-end">{{ formatCurrency(c.montant_paye) }}</td>
                                        <td class="text-end fw-semibold" :style="{color:c.solde>0?'#c62828':'#2e7d32'}">{{ formatCurrency(c.solde) }}</td>
                                        <td><span class="isup-status" :class="c.statut==='payee'?'isup-status-green':c.statut==='retard'?'isup-status-red':'isup-status-grey'">{{ c.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteCotisation(c.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pressing - Commandes -->
                <div v-if="activeTab==='pressing-commandes'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-basket me-2" style="color:#FF7900;"></i>Commandes pressing</span>
                            <button class="isup-btn-primary" @click="openPressingCommandeModal()"><i class="bi-plus-lg me-1"></i>Nouvelle commande</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="pressingCommandesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!pressingCommandes.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-basket" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune commande pressing.</p>
                                <button class="isup-btn-primary" @click="openPressingCommandeModal()">Créer une commande</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Commande</th><th>Client</th><th>Contact</th><th>Dépôt</th><th>Retrait prévu</th><th class="text-end">Articles</th><th>Service</th><th class="text-end">Total</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="c in pressingCommandes" :key="c.id">
                                        <td class="ps-4 fw-semibold">{{ c.numero_commande }}</td>
                                        <td>{{ c.client_nom||'-' }}</td>
                                        <td>{{ c.client_contact||'-' }}</td>
                                        <td>{{ c.date_depot||'-' }}</td>
                                        <td>{{ c.date_retrait_prevu||'-' }}</td>
                                        <td class="text-end">{{ c.nb_articles }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ c.type_service }}</span></td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(c.montant_total) }}</td>
                                        <td><span class="isup-status" :class="c.statut==='termine'||c.statut==='livre'?'isup-status-green':c.statut==='en_cours'?'isup-status-blue':'isup-status-grey'">{{ c.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deletePressingCommande(c.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transit - Fret -->
                <div v-if="activeTab==='transit-fret'">
                    <div class="isup-panel">
                        <div class="isup-panel-header"><span><i class="bi-truck me-2" style="color:#FF7900;"></i>Facturation fret</span></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="transitLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!transitDossiers.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-truck" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune facture fret.</p>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Réf.</th><th>Marchandise</th><th>Fournisseur</th><th class="text-end">Valeur</th><th class="text-end">Fret</th><th class="text-end">Douane</th><th class="text-end">Total</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="d in transitDossiers" :key="d.id">
                                        <td class="ps-4 fw-semibold">{{ d.reference_dossier }}</td>
                                        <td>{{ d.marchandise||'-' }}</td>
                                        <td>{{ d.fournisseur_nom||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(d.valeur_marchandise) }}</td>
                                        <td class="text-end">{{ formatCurrency(d.frais_transport||0) }}</td>
                                        <td class="text-end">{{ formatCurrency(d.frais_douane||0) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(d.total_facture) }}</td>
                                        <td><span class="isup-status" :class="d.statut==='paye'||d.statut==='finalise'?'isup-status-green':d.statut==='en_cours'?'isup-status-blue':'isup-status-grey'">{{ d.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteTransit(d.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transit - Dossiers -->
                <div v-if="activeTab==='transit-dossiers'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-folder me-2" style="color:#FF7900;"></i>Dossiers transit</span>
                            <button class="isup-btn-primary" @click="openTransitModal()"><i class="bi-plus-lg me-1"></i>Nouveau dossier</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="transitLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!transitDossiers.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-folder" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucun dossier transit.</p>
                                <button class="isup-btn-primary" @click="openTransitModal()">Créer un dossier</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Réf.</th><th>Type</th><th>Fournisseur</th><th>Marchandise</th><th>Douane</th><th>Ouverture</th><th class="text-end">Valeur</th><th class="text-end">Total facture</th><th class="text-end">Payé</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="d in transitDossiers" :key="d.id">
                                        <td class="ps-4 fw-semibold">{{ d.reference_dossier }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ d.type_transit }}</span></td>
                                        <td>{{ d.fournisseur_nom||'-' }}</td>
                                        <td>{{ d.marchandise||'-' }}</td>
                                        <td>{{ d.douane_bureau||'-' }}</td>
                                        <td>{{ d.date_ouverture||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(d.valeur_marchandise) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(d.total_facture) }}</td>
                                        <td class="text-end">{{ formatCurrency(d.montant_paye) }}</td>
                                        <td><span class="isup-status" :class="d.statut==='paye'||d.statut==='finalise'?'isup-status-green':d.statut==='en_cours'?'isup-status-blue':'isup-status-grey'">{{ d.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteTransit(d.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Morgue - Dépôts -->
                <div v-if="activeTab==='morgue-depots'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-plus-square me-2" style="color:#FF7900;"></i>Dépôts morgue</span>
                            <button class="isup-btn-primary" @click="openMorgueDepotModal()"><i class="bi-plus-lg me-1"></i>Nouveau dépôt</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="morgueDepotsLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!morgueDepots.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-plus-square" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucun dépôt en morgue.</p>
                                <button class="isup-btn-primary" @click="openMorgueDepotModal()">Enregistrer un dépôt</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Dossier</th><th>Défunt</th><th>Dépôt</th><th>Conservation</th><th class="text-end">Jours</th><th class="text-end">Tarif/J</th><th class="text-end">Total</th><th class="text-end">Payé</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="d in morgueDepots" :key="d.id">
                                        <td class="ps-4 fw-semibold">{{ d.numero_dossier }}</td>
                                        <td>{{ d.defunt_nom }} {{ d.defunt_prenom||'' }}</td>
                                        <td>{{ d.date_depot||'-' }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ d.type_conservation }}</span></td>
                                        <td class="text-end">{{ d.nb_jours }}</td>
                                        <td class="text-end">{{ formatCurrency(d.tarif_journalier) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(d.montant_total) }}</td>
                                        <td class="text-end">{{ formatCurrency(d.montant_paye) }}</td>
                                        <td><span class="isup-status" :class="d.statut==='cloture'||d.statut==='sorti'?'isup-status-green':d.statut==='en_cours'?'isup-status-blue':'isup-status-grey'">{{ d.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteMorgueDepot(d.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Morgue - Factures -->
                <div v-if="activeTab==='morgue-factures'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-receipt me-2" style="color:#FF7900;"></i>Facturation morgue</span>
                            <button class="isup-btn-primary" @click="openMorgueFactureModal()"><i class="bi-plus-lg me-1"></i>Nouvelle facture</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="morgueFacturesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!morgueFactures.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-receipt" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune facture morgue.</p>
                                <button class="isup-btn-primary" @click="openMorgueFactureModal()">Créer une facture</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Facture</th><th>Client</th><th>Défunt</th><th>Prestation</th><th class="text-end">Jours</th><th class="text-end">HT</th><th class="text-end">TVA</th><th class="text-end">TTC</th><th class="text-end">Payé</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="f in morgueFactures" :key="f.id">
                                        <td class="ps-4 fw-semibold">{{ f.numero_facture }}</td>
                                        <td>{{ f.client_nom||'-' }}</td>
                                        <td>{{ f.defunt_nom||'-' }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ f.type_prestation }}</span></td>
                                        <td class="text-end">{{ f.nb_jours }}</td>
                                        <td class="text-end">{{ formatCurrency(f.montant_ht) }}</td>
                                        <td class="text-end">{{ formatCurrency(f.tva) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(f.montant_ttc) }}</td>
                                        <td class="text-end">{{ formatCurrency(f.montant_paye) }}</td>
                                        <td><span class="isup-status" :class="f.statut==='payee'?'isup-status-green':f.statut==='partielle'?'isup-status-blue':'isup-status-grey'">{{ f.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteMorgueFacture(f.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trésorerie -->
                <div v-if="activeTab==='treasury'">
                    <div class="isup-panel">
                        <div class="isup-panel-header"><span><i class="bi-cash-stack me-2" style="color:#FF7900;"></i>Trésorerie</span></div>
                        <div class="isup-panel-body p-0">
                            <div v-if="treasuryLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!treasuryData" class="text-center py-5 isup-empty-cell">
                                <i class="bi-cash-stack" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune donnée de trésorerie.</p>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <div class="row g-3 p-3">
                                    <div class="col-md-4"><div class="isup-stats-card"><div class="isup-stats-label">Solde actuel</div><div class="isup-stats-value" style="color:#2e7d32;">{{ formatCurrency(treasuryData.solde_actuel||0) }}</div></div></div>
                                    <div class="col-md-4"><div class="isup-stats-card"><div class="isup-stats-label">Entrées (mois)</div><div class="isup-stats-value" style="color:#1565c0;">{{ formatCurrency(treasuryData.entrees_mois||0) }}</div></div></div>
                                    <div class="col-md-4"><div class="isup-stats-card"><div class="isup-stats-label">Sorties (mois)</div><div class="isup-stats-value" style="color:#c62828;">{{ formatCurrency(treasuryData.sorties_mois||0) }}</div></div></div>
                                </div>
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Date</th><th>Libellé</th><th class="text-end">Entrée</th><th class="text-end">Sortie</th><th class="text-end pe-4">Solde</th></tr></thead>
                                    <tbody><tr v-for="m in (treasuryData.mouvements||[])" :key="m.id">
                                        <td class="ps-4">{{ m.date||'-' }}</td>
                                        <td>{{ m.libelle||m.description||'-' }}</td>
                                        <td class="text-end" style="color:#2e7d32;">{{ m.type==='entree'||m.type==='credit'?formatCurrency(m.montant):'-' }}</td>
                                        <td class="text-end" style="color:#c62828;">{{ m.type==='sortie'||m.type==='debit'?formatCurrency(m.montant):'-' }}</td>
                                        <td class="text-end pe-4 fw-semibold">{{ formatCurrency(m.solde||0) }}</td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grilles tarifaires -->
                <div v-if="activeTab==='grilles-tarifaires'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-tags me-2" style="color:#FF7900;"></i>Grilles tarifaires</span>
                            <button class="isup-btn-primary" @click="openGrilleTarifaireModal()"><i class="bi-plus-lg me-1"></i>Nouveau tarif</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="grillesTarifairesLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!grillesTarifaires.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-tags" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune grille tarifaire.</p>
                                <button class="isup-btn-primary" @click="openGrilleTarifaireModal()">Créer un tarif</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Code</th><th>Désignation</th><th>Catégorie</th><th>Unité</th><th class="text-end">Prix unitaire</th><th class="text-end">TVA</th><th class="text-end">Remise max</th><th>Validité</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="g in grillesTarifaires" :key="g.id">
                                        <td class="ps-4 fw-semibold">{{ g.code }}</td>
                                        <td>{{ g.designation }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ g.categorie||'-' }}</span></td>
                                        <td>{{ g.unite||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(g.prix_unitaire) }}</td>
                                        <td class="text-end">{{ g.tva||'0' }}%</td>
                                        <td class="text-end">{{ g.remise_max||'0' }}%</td>
                                        <td>{{ g.date_validite_debut||'-' }} → {{ g.date_validite_fin||'-' }}</td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteGrilleTarifaire(g.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commissions -->
                <div v-if="activeTab==='commissions'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-percent me-2" style="color:#FF7900;"></i>Commissions</span>
                            <button class="isup-btn-primary" @click="openCommissionModal()"><i class="bi-plus-lg me-1"></i>Nouvelle commission</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="commissionsLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!commissions.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-percent" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune commission.</p>
                                <button class="isup-btn-primary" @click="openCommissionModal()">Calculer une commission</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">N° Commission</th><th>Type</th><th>Agent</th><th class="text-end">Base</th><th class="text-end">Taux</th><th class="text-end">Commission</th><th class="text-end">TVA</th><th class="text-end">Net</th><th class="text-end">Payé</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="c in commissions" :key="c.id">
                                        <td class="ps-4 fw-semibold">{{ c.numero_commission }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ c.type }}</span></td>
                                        <td>{{ c.agent_nom||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(c.montant_base) }}</td>
                                        <td class="text-end">{{ c.taux_commission }}%</td>
                                        <td class="text-end">{{ formatCurrency(c.montant_commission) }}</td>
                                        <td class="text-end">{{ formatCurrency(c.tva) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(c.montant_net) }}</td>
                                        <td class="text-end">{{ formatCurrency(c.montant_paye) }}</td>
                                        <td><span class="isup-status" :class="c.statut==='payee'?'isup-status-green':c.statut==='calculee'?'isup-status-blue':'isup-status-grey'">{{ c.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteCommission(c.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Money -->
                <div v-if="activeTab==='mobile-money'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-phone me-2" style="color:#FF7900;"></i>Mobile Money</span>
                            <button class="isup-btn-primary" @click="openMobileTransactionModal()"><i class="bi-plus-lg me-1"></i>Nouvelle transaction</button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="mobileTransactionsLoading" class="text-center py-5"><div class="isup-spinner"></div></div>
                            <div v-else-if="!mobileTransactions.length" class="text-center py-5 isup-empty-cell">
                                <i class="bi-phone" style="font-size:2.5rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>
                                <p>Aucune transaction Mobile Money.</p>
                                <button class="isup-btn-primary" @click="openMobileTransactionModal()">Enregistrer une transaction</button>
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead><tr><th class="ps-4">Réf.</th><th>Opérateur</th><th>Type</th><th>Expéditeur</th><th>Destinataire</th><th>Date</th><th class="text-end">Montant</th><th class="text-end">Frais</th><th class="text-end">Net</th><th>Statut</th><th class="text-end pe-4">Actions</th></tr></thead>
                                    <tbody><tr v-for="t in mobileTransactions" :key="t.id">
                                        <td class="ps-4 fw-semibold">{{ t.reference_transaction||'-' }}</td>
                                        <td><span class="isup-badge isup-badge-light">{{ t.operateur }}</span></td>
                                        <td>{{ t.type }}</td>
                                        <td>{{ t.nom_expediteur||t.numero_expediteur||'-' }}</td>
                                        <td>{{ t.nom_destinataire||t.numero_destinataire||'-' }}</td>
                                        <td>{{ t.date_transaction||'-' }}</td>
                                        <td class="text-end">{{ formatCurrency(t.montant) }}</td>
                                        <td class="text-end">{{ formatCurrency(t.frais) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(t.montant_net) }}</td>
                                        <td><span class="isup-status" :class="t.statut==='effectuee'?'isup-status-green':t.statut==='echouee'?'isup-status-grey':'isup-status-blue'">{{ t.statut }}</span></td>
                                        <td class="text-end pe-4"><button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteMobileTransaction(t.id)"><i class="bi-trash"></i></button></td>
                                    </tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rapports -->
                <div v-if="activeTab==='rapports'">
                    <div class="isup-panel">
                        <div class="isup-panel-header"><span><i class="bi-file-earmark-pdf me-2" style="color:#FF7900;"></i>Rapports</span></div>
                        <div class="isup-panel-body p-3">
                            <div class="row g-3">
                                <div class="col-md-4"><div class="isup-stats-card" style="cursor:pointer;" @click="onTabChange('bilan')"><div class="isup-stats-label">Bilan comptable</div><i class="bi-file-earmark-text" style="font-size:2rem;color:#FF7900;"></i><p class="small mt-2 mb-0" style="color:#888;">Voir le bilan</p></div></div>
                                <div class="col-md-4"><div class="isup-stats-card" style="cursor:pointer;" @click="onTabChange('resultat')"><div class="isup-stats-label">Compte de résultat</div><i class="bi-graph-up" style="font-size:2rem;color:#FF7900;"></i><p class="small mt-2 mb-0" style="color:#888;">Voir le résultat</p></div></div>
                                <div class="col-md-4"><div class="isup-stats-card" style="cursor:pointer;" @click="onTabChange('tva')"><div class="isup-stats-label">Déclarations TVA</div><i class="bi-cash" style="font-size:2rem;color:#FF7900;"></i><p class="small mt-2 mb-0" style="color:#888;">Voir la TVA</p></div></div>
                                <div class="col-md-4"><div class="isup-stats-card" style="cursor:pointer;" @click="onTabChange('treasury')"><div class="isup-stats-label">Trésorerie</div><i class="bi-wallet2" style="font-size:2rem;color:#FF7900;"></i><p class="small mt-2 mb-0" style="color:#888;">Voir la trésorerie</p></div></div>
                                <div class="col-md-4"><div class="isup-stats-card" style="cursor:pointer;" @click="onTabChange('journals')"><div class="isup-stats-label">Journaux</div><i class="bi-journal-text" style="font-size:2rem;color:#FF7900;"></i><p class="small mt-2 mb-0" style="color:#888;">Voir les journaux</p></div></div>
                                <div class="col-md-4"><div class="isup-stats-card" style="cursor:pointer;" @click="onTabChange('grand-livre')"><div class="isup-stats-label">Grand Livre</div><i class="bi-book" style="font-size:2rem;color:#FF7900;"></i><p class="small mt-2 mb-0" style="color:#888;">Voir le Grand Livre</p></div></div>
                            </div>
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

        <!-- ==================== MODAL STOCK ==================== -->
        <div v-if="showStockModal" class="isup-modal-overlay" @click.self="showStockModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-box-seam me-2"></i>Nouvel article en stock</span><button class="isup-modal-close" @click="showStockModal=false">&times;</button></div>
                <form @submit.prevent="submitStockItem">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">Référence *</label><input type="text" class="isup-input" v-model="stockForm.reference" placeholder="ART-001" required></div>
                            <div class="col-md-6"><label class="isup-label">Unité</label><select class="isup-select" v-model="stockForm.unit"><option value="unite">Unité</option><option value="kg">Kg</option><option value="litre">Litre</option><option value="m2">m²</option><option value="m3">m³</option><option value="carton">Carton</option><option value="palette">Palette</option></select></div>
                            <div class="col-12"><label class="isup-label">Désignation *</label><input type="text" class="isup-input" v-model="stockForm.designation" placeholder="Nom de l'article" required></div>
                            <div class="col-md-4"><label class="isup-label">Prix d'achat</label><input type="number" step="0.01" min="0" class="isup-input" v-model="stockForm.purchase_price" placeholder="0,00"></div>
                            <div class="col-md-4"><label class="isup-label">Prix de vente</label><input type="number" step="0.01" min="0" class="isup-input" v-model="stockForm.selling_price" placeholder="0,00"></div>
                            <div class="col-md-4"><label class="isup-label">Seuil d'alerte</label><input type="number" min="0" class="isup-input" v-model="stockForm.stock_alert" placeholder="0"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showStockModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="stockSubmitting"><span v-if="stockSubmitting" class="isup-spinner-sm me-1"></span>Créer l'article</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- ==================== MODAL MOUVEMENT STOCK ==================== -->
        <div v-if="showMovementModal" class="isup-modal-overlay" @click.self="showMovementModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i :class="movementType==='entry'?'bi-truck':'bi-cart3'"></i> {{ movementType==='entry'?'Entrée stock':'Sortie stock' }}</span><button class="isup-modal-close" @click="showMovementModal=false">&times;</button></div>
                <form @submit.prevent="submitMovement">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-12"><label class="isup-label">Article *</label><select class="isup-select" v-model="movementForm.erp_item_id" required><option value="" disabled>Sélectionner un article</option><option v-for="i in stockItems" :key="i.id" :value="i.id">{{ i.reference }} — {{ i.designation }} (stock: {{ i.stock }})</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Quantité *</label><input type="number" step="0.01" min="0.01" class="isup-input" v-model="movementForm.quantity" required></div>
                            <div class="col-md-4"><label class="isup-label">Date</label><input type="date" class="isup-input" v-model="movementForm.movement_date"></div>
                            <div class="col-md-4"><label class="isup-label">Type</label><div><span class="isup-badge" :class="movementType==='entry'?'isup-badge-green':'isup-badge-red'">{{ movementType==='entry'?'Entrée':'Sortie' }}</span></div></div>
                            <div class="col-12"><label class="isup-label">Motif</label><textarea class="isup-input" rows="2" v-model="movementForm.motif" placeholder="Raison du mouvement..."></textarea></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showMovementModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="movementSubmitting"><span v-if="movementSubmitting" class="isup-spinner-sm me-1"></span>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- ==================== MODAL EMBALLAGE ==================== -->
        <div v-if="showEmballageModal" class="isup-modal-overlay" @click.self="showEmballageModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-box me-2"></i>Nouvel emballage consigné</span><button class="isup-modal-close" @click="showEmballageModal=false">&times;</button></div>
                <form @submit.prevent="submitEmballage">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">Type *</label><select class="isup-select" v-model="emballageForm.type" required><option value="consigne">Consigné</option><option value="perdu">Perdu</option><option value="retourne">Retourné</option><option value="echange">Échangé</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Statut</label><select class="isup-select" v-model="emballageForm.statut"><option value="en_cours">En cours</option><option value="retourne">Retourné</option><option value="facture">Facturé</option><option value="perdu">Perdu</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Tiers</label><input type="text" class="isup-input" v-model="emballageForm.tiers_nom" placeholder="Nom du client/fournisseur"></div>
                            <div class="col-md-6"><label class="isup-label">Type tiers</label><select class="isup-select" v-model="emballageForm.tiers_type"><option value="client">Client</option><option value="fournisseur">Fournisseur</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Produit</label><input type="text" class="isup-input" v-model="emballageForm.produit" placeholder="Désignation"></div>
                            <div class="col-md-3"><label class="isup-label">Quantité *</label><input type="number" min="1" class="isup-input" v-model="emballageForm.quantite" required></div>
                            <div class="col-md-3"><label class="isup-label">Montant consigne</label><input type="number" step="0.01" min="0" class="isup-input" v-model="emballageForm.montant_consigne"></div>
                            <div class="col-md-6"><label class="isup-label">Date émission</label><input type="date" class="isup-input" v-model="emballageForm.date_emission"></div>
                            <div class="col-md-6"><label class="isup-label">Date retour</label><input type="date" class="isup-input" v-model="emballageForm.date_retour"></div>
                            <div class="col-12"><label class="isup-label">Notes</label><textarea class="isup-input" rows="2" v-model="emballageForm.notes"></textarea></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showEmballageModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="emballageSubmitting"><span v-if="emballageSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL HÔTEL FACTURE ==================== -->
        <div v-if="showHotelFactureModal" class="isup-modal-overlay" @click.self="showHotelFactureModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-receipt me-2"></i>Nouvelle facture hôtel</span><button class="isup-modal-close" @click="showHotelFactureModal=false">&times;</button></div>
                <form @submit.prevent="submitHotelFacture">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Facture *</label><input type="text" class="isup-input" v-model="hotelFactureForm.numero_facture" required></div>
                            <div class="col-md-6"><label class="isup-label">Type *</label><select class="isup-select" v-model="hotelFactureForm.type" required><option value="chambre">Chambre</option><option value="restauration">Restauration</option><option value="service">Service</option><option value="autre">Autre</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Client</label><input type="text" class="isup-input" v-model="hotelFactureForm.client_nom" placeholder="Nom du client"></div>
                            <div class="col-md-6"><label class="isup-label">Chambre</label><input type="text" class="isup-input" v-model="hotelFactureForm.chambre" placeholder="N° chambre"></div>
                            <div class="col-md-4"><label class="isup-label">Arrivée</label><input type="date" class="isup-input" v-model="hotelFactureForm.date_arrivee"></div>
                            <div class="col-md-4"><label class="isup-label">Départ</label><input type="date" class="isup-input" v-model="hotelFactureForm.date_depart"></div>
                            <div class="col-md-4"><label class="isup-label">Nuitées</label><input type="number" min="1" class="isup-input" v-model="hotelFactureForm.nb_nuitees"></div>
                            <div class="col-md-4"><label class="isup-label">Prix nuitée</label><input type="number" step="0.01" min="0" class="isup-input" v-model="hotelFactureForm.prix_nuitee"></div>
                            <div class="col-md-4"><label class="isup-label">Montant HT</label><input type="number" step="0.01" min="0" class="isup-input" v-model="hotelFactureForm.montant_ht"></div>
                            <div class="col-md-4"><label class="isup-label">TVA</label><input type="number" step="0.01" min="0" class="isup-input" v-model="hotelFactureForm.tva"></div>
                            <div class="col-md-4"><label class="isup-label">Taxe séjour</label><input type="number" step="0.01" min="0" class="isup-input" v-model="hotelFactureForm.taxe_sejour"></div>
                            <div class="col-md-4"><label class="isup-label">Montant payé</label><input type="number" step="0.01" min="0" class="isup-input" v-model="hotelFactureForm.montant_paye"></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="hotelFactureForm.statut"><option value="en_attente">En attente</option><option value="payee">Payée</option><option value="partielle">Partielle</option><option value="annulee">Annulée</option></select></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showHotelFactureModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="hotelFactureSubmitting"><span v-if="hotelFactureSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL SCOLAIRE FACTURE ==================== -->
        <div v-if="showScolaireFactureModal" class="isup-modal-overlay" @click.self="showScolaireFactureModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-mortarboard me-2"></i>Nouvelle facture scolaire</span><button class="isup-modal-close" @click="showScolaireFactureModal=false">&times;</button></div>
                <form @submit.prevent="submitScolaireFacture">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Facture *</label><input type="text" class="isup-input" v-model="scolaireFactureForm.numero_facture" required></div>
                            <div class="col-md-6"><label class="isup-label">Année scolaire *</label><input type="text" class="isup-input" v-model="scolaireFactureForm.annee_scolaire" placeholder="2024/2025" required></div>
                            <div class="col-md-6"><label class="isup-label">Nom élève *</label><input type="text" class="isup-input" v-model="scolaireFactureForm.eleve_nom" required></div>
                            <div class="col-md-6"><label class="isup-label">Prénom élève</label><input type="text" class="isup-input" v-model="scolaireFactureForm.eleve_prenom"></div>
                            <div class="col-md-4"><label class="isup-label">Classe *</label><input type="text" class="isup-input" v-model="scolaireFactureForm.classe" required></div>
                            <div class="col-md-4"><label class="isup-label">Matricule</label><input type="text" class="isup-input" v-model="scolaireFactureForm.matricule"></div>
                            <div class="col-md-4"><label class="isup-label">Type frais *</label><select class="isup-select" v-model="scolaireFactureForm.type_frais" required><option value="scolarite">Scolarité</option><option value="inscription">Inscription</option><option value="cantine">Cantine</option><option value="pension">Pension</option><option value="transport">Transport</option><option value="tenue">Tenue</option><option value="autre">Autre</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Période</label><input type="text" class="isup-input" v-model="scolaireFactureForm.periode" placeholder="1er trimestre"></div>
                            <div class="col-md-4"><label class="isup-label">Montant dû</label><input type="number" step="0.01" min="0" class="isup-input" v-model="scolaireFactureForm.montant_du"></div>
                            <div class="col-md-4"><label class="isup-label">Remise</label><input type="number" step="0.01" min="0" class="isup-input" v-model="scolaireFactureForm.remise"></div>
                            <div class="col-md-4"><label class="isup-label">Montant payé</label><input type="number" step="0.01" min="0" class="isup-input" v-model="scolaireFactureForm.montant_paye"></div>
                            <div class="col-md-4"><label class="isup-label">Date échéance</label><input type="date" class="isup-input" v-model="scolaireFactureForm.date_echeance"></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="scolaireFactureForm.statut"><option value="en_attente">En attente</option><option value="payee">Payée</option><option value="partielle">Partielle</option><option value="impayee">Impayée</option><option value="annulee">Annulée</option></select></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showScolaireFactureModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="scolaireFactureSubmitting"><span v-if="scolaireFactureSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL QUITTANCE ==================== -->
        <div v-if="showQuittanceModal" class="isup-modal-overlay" @click.self="showQuittanceModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-file-text me-2"></i>Nouvelle quittance</span><button class="isup-modal-close" @click="showQuittanceModal=false">&times;</button></div>
                <form @submit.prevent="submitQuittance">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Quittance *</label><input type="text" class="isup-input" v-model="quittanceForm.numero_quittance" required></div>
                            <div class="col-md-6"><label class="isup-label">Bien</label><input type="text" class="isup-input" v-model="quittanceForm.bien" placeholder="Adresse ou ref. du bien"></div>
                            <div class="col-md-6"><label class="isup-label">Locataire *</label><input type="text" class="isup-input" v-model="quittanceForm.locataire_nom" required></div>
                            <div class="col-md-6"><label class="isup-label">Période *</label><input type="text" class="isup-input" v-model="quittanceForm.periode" placeholder="2024-01" required></div>
                            <div class="col-md-4"><label class="isup-label">Début *</label><input type="date" class="isup-input" v-model="quittanceForm.date_debut" required></div>
                            <div class="col-md-4"><label class="isup-label">Fin *</label><input type="date" class="isup-input" v-model="quittanceForm.date_fin" required></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="quittanceForm.statut"><option value="en_attente">En attente</option><option value="payee">Payée</option><option value="partielle">Partielle</option><option value="impayee">Impayée</option><option value="annulee">Annulée</option></select></div>
                            <div class="col-md-3"><label class="isup-label">Loyer HT</label><input type="number" step="0.01" min="0" class="isup-input" v-model="quittanceForm.loyer_ht"></div>
                            <div class="col-md-3"><label class="isup-label">Charges</label><input type="number" step="0.01" min="0" class="isup-input" v-model="quittanceForm.charges"></div>
                            <div class="col-md-3"><label class="isup-label">TVA</label><input type="number" step="0.01" min="0" class="isup-input" v-model="quittanceForm.tva"></div>
                            <div class="col-md-3"><label class="isup-label">Montant payé</label><input type="number" step="0.01" min="0" class="isup-input" v-model="quittanceForm.montant_paye"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showQuittanceModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="quittanceSubmitting"><span v-if="quittanceSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL COTISATION ==================== -->
        <div v-if="showCotisationModal" class="isup-modal-overlay" @click.self="showCotisationModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-coin me-2"></i>Nouvelle cotisation</span><button class="isup-modal-close" @click="showCotisationModal=false">&times;</button></div>
                <form @submit.prevent="submitCotisation">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">Tontine *</label><input type="text" class="isup-input" v-model="cotisationForm.tontine_nom" placeholder="Nom du groupe" required></div>
                            <div class="col-md-6"><label class="isup-label">Membre *</label><input type="text" class="isup-input" v-model="cotisationForm.membre_nom" required></div>
                            <div class="col-md-4"><label class="isup-label">Période *</label><input type="text" class="isup-input" v-model="cotisationForm.periode" placeholder="2024-01" required></div>
                            <div class="col-md-4"><label class="isup-label">Échéance *</label><input type="date" class="isup-input" v-model="cotisationForm.date_echeance" required></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="cotisationForm.statut"><option value="en_attente">En attente</option><option value="payee">Payée</option><option value="retard">En retard</option><option value="annulee">Annulée</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Montant *</label><input type="number" step="0.01" min="0" class="isup-input" v-model="cotisationForm.montant" required></div>
                            <div class="col-md-6"><label class="isup-label">Montant payé</label><input type="number" step="0.01" min="0" class="isup-input" v-model="cotisationForm.montant_paye"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showCotisationModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="cotisationSubmitting"><span v-if="cotisationSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL TRANSIT ==================== -->
        <div v-if="showTransitModal" class="isup-modal-overlay" @click.self="showTransitModal=false">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header"><span><i class="bi-folder me-2"></i>Nouveau dossier transit</span><button class="isup-modal-close" @click="showTransitModal=false">&times;</button></div>
                <form @submit.prevent="submitTransit">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">Réf. dossier *</label><input type="text" class="isup-input" v-model="transitForm.reference_dossier" required></div>
                            <div class="col-md-6"><label class="isup-label">Type transit *</label><select class="isup-select" v-model="transitForm.type_transit" required><option value="import">Import</option><option value="export">Export</option><option value="transitaire">Transitaire</option><option value="douane">Douane</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Fournisseur</label><input type="text" class="isup-input" v-model="transitForm.fournisseur_nom"></div>
                            <div class="col-md-6"><label class="isup-label">Bureau douane</label><input type="text" class="isup-input" v-model="transitForm.douane_bureau"></div>
                            <div class="col-md-6"><label class="isup-label">Marchandise</label><input type="text" class="isup-input" v-model="transitForm.marchandise"></div>
                            <div class="col-md-3"><label class="isup-label">Date ouverture *</label><input type="date" class="isup-input" v-model="transitForm.date_ouverture" required></div>
                            <div class="col-md-3"><label class="isup-label">Statut</label><select class="isup-select" v-model="transitForm.statut"><option value="en_cours">En cours</option><option value="finalise">Finalisé</option><option value="facture">Facturé</option><option value="paye">Payé</option><option value="annule">Annulé</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Valeur marchandise</label><input type="number" step="0.01" min="0" class="isup-input" v-model="transitForm.valeur_marchandise"></div>
                            <div class="col-md-4"><label class="isup-label">Fret HT</label><input type="number" step="0.01" min="0" class="isup-input" v-model="transitForm.fret_ht"></div>
                            <div class="col-md-4"><label class="isup-label">Droits douane</label><input type="number" step="0.01" min="0" class="isup-input" v-model="transitForm.droits_douane"></div>
                            <div class="col-md-4"><label class="isup-label">TVA douane</label><input type="number" step="0.01" min="0" class="isup-input" v-model="transitForm.tva_douane"></div>
                            <div class="col-md-4"><label class="isup-label">Frais accessoires</label><input type="number" step="0.01" min="0" class="isup-input" v-model="transitForm.frais_accessoires"></div>
                            <div class="col-md-4"><label class="isup-label">Montant payé</label><input type="number" step="0.01" min="0" class="isup-input" v-model="transitForm.montant_paye"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showTransitModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="transitSubmitting"><span v-if="transitSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- ==================== MODAL CHAMBRE ==================== -->
        <div v-if="showHotelChambreModal" class="isup-modal-overlay" @click.self="showHotelChambreModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-door-open me-2"></i>Nouvelle chambre</span><button class="isup-modal-close" @click="showHotelChambreModal=false">&times;</button></div>
                <form @submit.prevent="submitHotelChambre">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Chambre *</label><input class="isup-input" v-model="hotelChambreForm.numero_chambre" required></div>
                            <div class="col-md-6"><label class="isup-label">Type *</label><select class="isup-select" v-model="hotelChambreForm.type" required><option value="standard">Standard</option><option value="suite">Suite</option><option value="vip">VIP</option><option value="presidentielle">Présidentielle</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Catégorie</label><input class="isup-input" v-model="hotelChambreForm.categorie"></div>
                            <div class="col-md-3"><label class="isup-label">Prix/nuitée *</label><input type="number" step="0.01" class="isup-input" v-model="hotelChambreForm.prix_nuitee" required></div>
                            <div class="col-md-3"><label class="isup-label">Capacité</label><input type="number" class="isup-input" v-model="hotelChambreForm.capacite"></div>
                            <div class="col-md-3"><label class="isup-label">Étage</label><input type="number" class="isup-input" v-model="hotelChambreForm.etage"></div>
                            <div class="col-md-3"><label class="isup-label">Statut</label><select class="isup-select" v-model="hotelChambreForm.statut"><option value="disponible">Disponible</option><option value="occupee">Occupée</option><option value="maintenance">Maintenance</option></select></div>
                            <div class="col-md-12"><label class="isup-label">Équipements</label><textarea class="isup-input" rows="2" v-model="hotelChambreForm.equipements"></textarea></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showHotelChambreModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="hotelChambreSubmitting"><span v-if="hotelChambreSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL RÉSERVATION ==================== -->
        <div v-if="showHotelReservationModal" class="isup-modal-overlay" @click.self="showHotelReservationModal=false">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header"><span><i class="bi-calendar-check me-2"></i>Nouvelle réservation</span><button class="isup-modal-close" @click="showHotelReservationModal=false">&times;</button></div>
                <form @submit.prevent="submitHotelReservation">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Réservation</label><input class="isup-input" v-model="hotelReservationForm.numero_reservation"></div>
                            <div class="col-md-6"><label class="isup-label">Chambre *</label><input class="isup-input" v-model="hotelReservationForm.chambre_id" required></div>
                            <div class="col-md-6"><label class="isup-label">Client</label><input class="isup-input" v-model="hotelReservationForm.client_nom"></div>
                            <div class="col-md-6"><label class="isup-label">Contact</label><input class="isup-input" v-model="hotelReservationForm.client_contact"></div>
                            <div class="col-md-4"><label class="isup-label">Arrivée *</label><input type="date" class="isup-input" v-model="hotelReservationForm.date_arrivee" required></div>
                            <div class="col-md-4"><label class="isup-label">Départ *</label><input type="date" class="isup-input" v-model="hotelReservationForm.date_depart" required></div>
                            <div class="col-md-2"><label class="isup-label">Nuitées</label><input type="number" class="isup-input" v-model="hotelReservationForm.nb_nuitees"></div>
                            <div class="col-md-2"><label class="isup-label">Adultes</label><input type="number" class="isup-input" v-model="hotelReservationForm.nb_adultes"></div>
                            <div class="col-md-4"><label class="isup-label">Total</label><input type="number" step="0.01" class="isup-input" v-model="hotelReservationForm.montant_total"></div>
                            <div class="col-md-4"><label class="isup-label">Acompte</label><input type="number" step="0.01" class="isup-input" v-model="hotelReservationForm.acompte"></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="hotelReservationForm.statut"><option value="confirmee">Confirmée</option><option value="en_attente">En attente</option><option value="annulee">Annulée</option><option value="terminee">Terminée</option></select></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showHotelReservationModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="hotelReservationSubmitting"><span v-if="hotelReservationSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL ÉLÈVE ==================== -->
        <div v-if="showScolaireEleveModal" class="isup-modal-overlay" @click.self="showScolaireEleveModal=false">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header"><span><i class="bi-people me-2"></i>Nouvel élève</span><button class="isup-modal-close" @click="showScolaireEleveModal=false">&times;</button></div>
                <form @submit.prevent="submitScolaireEleve">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="isup-label">Matricule</label><input class="isup-input" v-model="scolaireEleveForm.matricule"></div>
                            <div class="col-md-4"><label class="isup-label">Nom *</label><input class="isup-input" v-model="scolaireEleveForm.nom" required></div>
                            <div class="col-md-4"><label class="isup-label">Prénom</label><input class="isup-input" v-model="scolaireEleveForm.prenom"></div>
                            <div class="col-md-3"><label class="isup-label">Date naissance</label><input type="date" class="isup-input" v-model="scolaireEleveForm.date_naissance"></div>
                            <div class="col-md-3"><label class="isup-label">Sexe</label><select class="isup-select" v-model="scolaireEleveForm.sexe"><option value="M">Masculin</option><option value="F">Féminin</option></select></div>
                            <div class="col-md-3"><label class="isup-label">Classe *</label><input class="isup-input" v-model="scolaireEleveForm.classe" required></div>
                            <div class="col-md-3"><label class="isup-label">Niveau</label><input class="isup-input" v-model="scolaireEleveForm.niveau"></div>
                            <div class="col-md-4"><label class="isup-label">Année scolaire</label><input class="isup-input" v-model="scolaireEleveForm.annee_scolaire"></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="scolaireEleveForm.statut"><option value="actif">Actif</option><option value="inactif">Inactif</option><option value="diplome">Diplômé</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Tuteur</label><input class="isup-input" v-model="scolaireEleveForm.nom_tuteur"></div>
                            <div class="col-md-6"><label class="isup-label">Contact tuteur</label><input class="isup-input" v-model="scolaireEleveForm.contact_tuteur"></div>
                            <div class="col-md-6"><label class="isup-label">Email tuteur</label><input type="email" class="isup-input" v-model="scolaireEleveForm.email_tuteur"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showScolaireEleveModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="scolaireEleveSubmitting"><span v-if="scolaireEleveSubmitting" class="isup-spinner-sm me-1"></span>Inscrire</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL BIEN ==================== -->
        <div v-if="showLocationBienModal" class="isup-modal-overlay" @click.self="showLocationBienModal=false">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header"><span><i class="bi-building me-2"></i>Nouveau bien</span><button class="isup-modal-close" @click="showLocationBienModal=false">&times;</button></div>
                <form @submit.prevent="submitLocationBien">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">Référence</label><input class="isup-input" v-model="locationBienForm.reference_bien"></div>
                            <div class="col-md-6"><label class="isup-label">Désignation *</label><input class="isup-input" v-model="locationBienForm.designation" required></div>
                            <div class="col-md-4"><label class="isup-label">Type</label><select class="isup-select" v-model="locationBienForm.type"><option value="appartement">Appartement</option><option value="maison">Maison</option><option value="villa">Villa</option><option value="bureau">Bureau</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Ville</label><input class="isup-input" v-model="locationBienForm.ville"></div>
                            <div class="col-md-4"><label class="isup-label">Quartier</label><input class="isup-input" v-model="locationBienForm.quartier"></div>
                            <div class="col-md-3"><label class="isup-label">Surface</label><input type="number" class="isup-input" v-model="locationBienForm.surface"></div>
                            <div class="col-md-3"><label class="isup-label">Pièces</label><input type="number" class="isup-input" v-model="locationBienForm.nb_pieces"></div>
                            <div class="col-md-3"><label class="isup-label">Loyer *</label><input type="number" step="0.01" class="isup-input" v-model="locationBienForm.loyer_mensuel" required></div>
                            <div class="col-md-3"><label class="isup-label">Charges</label><input type="number" step="0.01" class="isup-input" v-model="locationBienForm.charges_mensuelles"></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="locationBienForm.statut"><option value="disponible">Disponible</option><option value="loue">Loué</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Locataire</label><input class="isup-input" v-model="locationBienForm.locataire_actuel"></div>
                            <div class="col-md-4"><label class="isup-label">Caution</label><input type="number" step="0.01" class="isup-input" v-model="locationBienForm.caution"></div>
                            <div class="col-md-6"><label class="isup-label">Début bail</label><input type="date" class="isup-input" v-model="locationBienForm.date_debut_bail"></div>
                            <div class="col-md-6"><label class="isup-label">Fin bail</label><input type="date" class="isup-input" v-model="locationBienForm.date_fin_bail"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showLocationBienModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="locationBienSubmitting"><span v-if="locationBienSubmitting" class="isup-spinner-sm me-1"></span>Ajouter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL TONTINE ==================== -->
        <div v-if="showTontineModal" class="isup-modal-overlay" @click.self="showTontineModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-people me-2"></i>Nouveau groupe</span><button class="isup-modal-close" @click="showTontineModal=false">&times;</button></div>
                <form @submit.prevent="submitTontine">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">Nom du groupe *</label><input class="isup-input" v-model="tontineForm.nom_groupe" required></div>
                            <div class="col-md-6"><label class="isup-label">Membres</label><input type="number" class="isup-input" v-model="tontineForm.nb_membres"></div>
                            <div class="col-md-6"><label class="isup-label">Cotisation *</label><input type="number" step="0.01" class="isup-input" v-model="tontineForm.montant_cotisation" required></div>
                            <div class="col-md-6"><label class="isup-label">Fréquence</label><select class="isup-select" v-model="tontineForm.frequence"><option value="hebdomadaire">Hebdo</option><option value="mensuelle">Mensuelle</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Caisse</label><input type="number" step="0.01" class="isup-input" v-model="tontineForm.montant_caisse"></div>
                            <div class="col-md-6"><label class="isup-label">Date création</label><input type="date" class="isup-input" v-model="tontineForm.date_creation"></div>
                            <div class="col-md-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="2" v-model="tontineForm.description"></textarea></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showTontineModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="tontineSubmitting"><span v-if="tontineSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL PRESSING ==================== -->
        <div v-if="showPressingCommandeModal" class="isup-modal-overlay" @click.self="showPressingCommandeModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-basket me-2"></i>Nouvelle commande</span><button class="isup-modal-close" @click="showPressingCommandeModal=false">&times;</button></div>
                <form @submit.prevent="submitPressingCommande">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Commande</label><input class="isup-input" v-model="pressingCommandeForm.numero_commande"></div>
                            <div class="col-md-6"><label class="isup-label">Client *</label><input class="isup-input" v-model="pressingCommandeForm.client_nom" required></div>
                            <div class="col-md-6"><label class="isup-label">Contact</label><input class="isup-input" v-model="pressingCommandeForm.client_contact"></div>
                            <div class="col-md-3"><label class="isup-label">Dépôt *</label><input type="date" class="isup-input" v-model="pressingCommandeForm.date_depot" required></div>
                            <div class="col-md-3"><label class="isup-label">Retrait prévu</label><input type="date" class="isup-input" v-model="pressingCommandeForm.date_retrait_prevu"></div>
                            <div class="col-md-3"><label class="isup-label">Articles</label><input type="number" class="isup-input" v-model="pressingCommandeForm.nb_articles"></div>
                            <div class="col-md-3"><label class="isup-label">Service</label><select class="isup-select" v-model="pressingCommandeForm.type_service"><option value="nettoyage">Nettoyage</option><option value="repassage">Repassage</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Total *</label><input type="number" step="0.01" class="isup-input" v-model="pressingCommandeForm.montant_total" required></div>
                            <div class="col-md-4"><label class="isup-label">Acompte</label><input type="number" step="0.01" class="isup-input" v-model="pressingCommandeForm.acompte"></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="pressingCommandeForm.statut"><option value="en_cours">En cours</option><option value="pret">Prêt</option><option value="livre">Livré</option></select></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showPressingCommandeModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="pressingCommandeSubmitting"><span v-if="pressingCommandeSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL MORGUE DÉPÔT ==================== -->
        <div v-if="showMorgueDepotModal" class="isup-modal-overlay" @click.self="showMorgueDepotModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-plus-square me-2"></i>Nouveau dépôt</span><button class="isup-modal-close" @click="showMorgueDepotModal=false">&times;</button></div>
                <form @submit.prevent="submitMorgueDepot">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Dossier</label><input class="isup-input" v-model="morgueDepotForm.numero_dossier"></div>
                            <div class="col-md-6"><label class="isup-label">Conservation *</label><select class="isup-select" v-model="morgueDepotForm.type_conservation" required><option value="normale">Normale</option><option value="special">Spécial</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Nom défunt *</label><input class="isup-input" v-model="morgueDepotForm.defunt_nom" required></div>
                            <div class="col-md-4"><label class="isup-label">Prénom</label><input class="isup-input" v-model="morgueDepotForm.defunt_prenom"></div>
                            <div class="col-md-4"><label class="isup-label">Date décès</label><input type="date" class="isup-input" v-model="morgueDepotForm.date_deces"></div>
                            <div class="col-md-4"><label class="isup-label">Dépôt *</label><input type="date" class="isup-input" v-model="morgueDepotForm.date_depot" required></div>
                            <div class="col-md-4"><label class="isup-label">Sortie</label><input type="date" class="isup-input" v-model="morgueDepotForm.date_sortie"></div>
                            <div class="col-md-4"><label class="isup-label">Contact famille</label><input class="isup-input" v-model="morgueDepotForm.famille_contact"></div>
                            <div class="col-md-4"><label class="isup-label">Jours</label><input type="number" class="isup-input" v-model="morgueDepotForm.nb_jours"></div>
                            <div class="col-md-4"><label class="isup-label">Tarif/J</label><input type="number" step="0.01" class="isup-input" v-model="morgueDepotForm.tarif_journalier"></div>
                            <div class="col-md-4"><label class="isup-label">Total</label><input type="number" step="0.01" class="isup-input" v-model="morgueDepotForm.montant_total"></div>
                            <div class="col-md-4"><label class="isup-label">Payé</label><input type="number" step="0.01" class="isup-input" v-model="morgueDepotForm.montant_paye"></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="morgueDepotForm.statut"><option value="en_cours">En cours</option><option value="sorti">Sorti</option></select></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showMorgueDepotModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="morgueDepotSubmitting"><span v-if="morgueDepotSubmitting" class="isup-spinner-sm me-1"></span>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL MORGUE FACTURE ==================== -->
        <div v-if="showMorgueFactureModal" class="isup-modal-overlay" @click.self="showMorgueFactureModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-receipt me-2"></i>Nouvelle facture</span><button class="isup-modal-close" @click="showMorgueFactureModal=false">&times;</button></div>
                <form @submit.prevent="submitMorgueFacture">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Facture</label><input class="isup-input" v-model="morgueFactureForm.numero_facture"></div>
                            <div class="col-md-6"><label class="isup-label">Dépôt</label><input class="isup-input" v-model="morgueFactureForm.depot_id"></div>
                            <div class="col-md-6"><label class="isup-label">Client</label><input class="isup-input" v-model="morgueFactureForm.client_nom"></div>
                            <div class="col-md-6"><label class="isup-label">Défunt</label><input class="isup-input" v-model="morgueFactureForm.defunt_nom"></div>
                            <div class="col-md-4"><label class="isup-label">Prestation</label><select class="isup-select" v-model="morgueFactureForm.type_prestation"><option value="conservation">Conservation</option><option value="soins">Soins</option></select></div>
                            <div class="col-md-2"><label class="isup-label">Jours</label><input type="number" class="isup-input" v-model="morgueFactureForm.nb_jours"></div>
                            <div class="col-md-3"><label class="isup-label">HT</label><input type="number" step="0.01" class="isup-input" v-model="morgueFactureForm.montant_ht"></div>
                            <div class="col-md-3"><label class="isup-label">TVA</label><input type="number" step="0.01" class="isup-input" v-model="morgueFactureForm.tva"></div>
                            <div class="col-md-4"><label class="isup-label">TTC</label><input type="number" step="0.01" class="isup-input" v-model="morgueFactureForm.montant_ttc"></div>
                            <div class="col-md-4"><label class="isup-label">Payé</label><input type="number" step="0.01" class="isup-input" v-model="morgueFactureForm.montant_paye"></div>
                            <div class="col-md-4"><label class="isup-label">Statut</label><select class="isup-select" v-model="morgueFactureForm.statut"><option value="en_attente">En attente</option><option value="payee">Payée</option></select></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showMorgueFactureModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="morgueFactureSubmitting"><span v-if="morgueFactureSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL GRILLE TARIFAIRE ==================== -->
        <div v-if="showGrilleTarifaireModal" class="isup-modal-overlay" @click.self="showGrilleTarifaireModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-tags me-2"></i>Nouveau tarif</span><button class="isup-modal-close" @click="showGrilleTarifaireModal=false">&times;</button></div>
                <form @submit.prevent="submitGrilleTarifaire">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="isup-label">Code *</label><input class="isup-input" v-model="grilleTarifaireForm.code" required></div>
                            <div class="col-md-8"><label class="isup-label">Désignation *</label><input class="isup-input" v-model="grilleTarifaireForm.designation" required></div>
                            <div class="col-md-4"><label class="isup-label">Catégorie</label><input class="isup-input" v-model="grilleTarifaireForm.categorie"></div>
                            <div class="col-md-4"><label class="isup-label">Unité</label><input class="isup-input" v-model="grilleTarifaireForm.unite"></div>
                            <div class="col-md-4"><label class="isup-label">PU *</label><input type="number" step="0.01" class="isup-input" v-model="grilleTarifaireForm.prix_unitaire" required></div>
                            <div class="col-md-4"><label class="isup-label">TVA %</label><input type="number" step="0.01" class="isup-input" v-model="grilleTarifaireForm.tva"></div>
                            <div class="col-md-4"><label class="isup-label">Remise max %</label><input type="number" step="0.01" class="isup-input" v-model="grilleTarifaireForm.remise_max"></div>
                            <div class="col-md-6"><label class="isup-label">Validité début</label><input type="date" class="isup-input" v-model="grilleTarifaireForm.date_validite_debut"></div>
                            <div class="col-md-6"><label class="isup-label">Validité fin</label><input type="date" class="isup-input" v-model="grilleTarifaireForm.date_validite_fin"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showGrilleTarifaireModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="grilleTarifaireSubmitting"><span v-if="grilleTarifaireSubmitting" class="isup-spinner-sm me-1"></span>Créer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL COMMISSION ==================== -->
        <div v-if="showCommissionModal" class="isup-modal-overlay" @click.self="showCommissionModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-percent me-2"></i>Nouvelle commission</span><button class="isup-modal-close" @click="showCommissionModal=false">&times;</button></div>
                <form @submit.prevent="submitCommission">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">N° Commission</label><input class="isup-input" v-model="commissionForm.numero_commission"></div>
                            <div class="col-md-6"><label class="isup-label">Type</label><select class="isup-select" v-model="commissionForm.type"><option value="vente">Vente</option><option value="prestation">Prestation</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Agent *</label><input class="isup-input" v-model="commissionForm.agent_nom" required></div>
                            <div class="col-md-6"><label class="isup-label">Contact</label><input class="isup-input" v-model="commissionForm.agent_contact"></div>
                            <div class="col-md-4"><label class="isup-label">Base *</label><input type="number" step="0.01" class="isup-input" v-model="commissionForm.montant_base" required></div>
                            <div class="col-md-4"><label class="isup-label">Taux % *</label><input type="number" step="0.01" class="isup-input" v-model="commissionForm.taux_commission" required></div>
                            <div class="col-md-4"><label class="isup-label">Commission</label><input type="number" step="0.01" class="isup-input" v-model="commissionForm.montant_commission"></div>
                            <div class="col-md-3"><label class="isup-label">TVA</label><input type="number" step="0.01" class="isup-input" v-model="commissionForm.tva"></div>
                            <div class="col-md-3"><label class="isup-label">Net</label><input type="number" step="0.01" class="isup-input" v-model="commissionForm.montant_net"></div>
                            <div class="col-md-3"><label class="isup-label">Payé</label><input type="number" step="0.01" class="isup-input" v-model="commissionForm.montant_paye"></div>
                            <div class="col-md-3"><label class="isup-label">Statut</label><select class="isup-select" v-model="commissionForm.statut"><option value="calculee">Calculée</option><option value="payee">Payée</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Date opération</label><input type="date" class="isup-input" v-model="commissionForm.date_operation"></div>
                            <div class="col-md-6"><label class="isup-label">Date paiement</label><input type="date" class="isup-input" v-model="commissionForm.date_paiement"></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showCommissionModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="commissionSubmitting"><span v-if="commissionSubmitting" class="isup-spinner-sm me-1"></span>Calculer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL MOBILE MONEY ==================== -->
        <div v-if="showMobileTransactionModal" class="isup-modal-overlay" @click.self="showMobileTransactionModal=false">
            <div class="isup-modal">
                <div class="isup-modal-header"><span><i class="bi-phone me-2"></i>Nouvelle transaction</span><button class="isup-modal-close" @click="showMobileTransactionModal=false">&times;</button></div>
                <form @submit.prevent="submitMobileTransaction">
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="isup-label">Référence</label><input class="isup-input" v-model="mobileTransactionForm.reference_transaction"></div>
                            <div class="col-md-6"><label class="isup-label">Opérateur *</label><select class="isup-select" v-model="mobileTransactionForm.operateur" required><option value="mtn">MTN</option><option value="orange">Orange</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Type *</label><select class="isup-select" v-model="mobileTransactionForm.type" required><option value="depot">Dépôt</option><option value="retrait">Retrait</option></select></div>
                            <div class="col-md-4"><label class="isup-label">Montant *</label><input type="number" step="0.01" class="isup-input" v-model="mobileTransactionForm.montant" required></div>
                            <div class="col-md-4"><label class="isup-label">Frais</label><input type="number" step="0.01" class="isup-input" v-model="mobileTransactionForm.frais"></div>
                            <div class="col-md-6"><label class="isup-label">Expéditeur</label><input class="isup-input" v-model="mobileTransactionForm.nom_expediteur"></div>
                            <div class="col-md-6"><label class="isup-label">N° Expéditeur</label><input class="isup-input" v-model="mobileTransactionForm.numero_expediteur"></div>
                            <div class="col-md-6"><label class="isup-label">Destinataire</label><input class="isup-input" v-model="mobileTransactionForm.nom_destinataire"></div>
                            <div class="col-md-6"><label class="isup-label">N° Destinataire</label><input class="isup-input" v-model="mobileTransactionForm.numero_destinataire"></div>
                            <div class="col-md-6"><label class="isup-label">Date transaction *</label><input type="datetime-local" class="isup-input" v-model="mobileTransactionForm.date_transaction" required></div>
                            <div class="col-md-3"><label class="isup-label">Solde avant</label><input type="number" step="0.01" class="isup-input" v-model="mobileTransactionForm.solde_avant"></div>
                            <div class="col-md-3"><label class="isup-label">Solde après</label><input type="number" step="0.01" class="isup-input" v-model="mobileTransactionForm.solde_apres"></div>
                            <div class="col-md-6"><label class="isup-label">Statut</label><select class="isup-select" v-model="mobileTransactionForm.statut"><option value="effectuee">Effectuée</option><option value="echouee">Échouée</option></select></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button type="button" class="isup-btn-grey" @click="showMobileTransactionModal=false">Annuler</button>
                        <button type="submit" class="isup-btn-primary" :disabled="mobileTransactionSubmitting"><span v-if="mobileTransactionSubmitting" class="isup-spinner-sm me-1"></span>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </CompanyLayout>
</template>
