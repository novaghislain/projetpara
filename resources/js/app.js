import '../css/app.css';
import '../css/company.css';
import './bootstrap';

import { createApp } from 'vue';
import { initAuth } from './stores/auth';

// Initialize auth state from Blade-embedded JSON before Vue mounts
initAuth();

import GelLayout from './Layouts/GelLayout.vue';

// Page Components — Gel
import GelDashboard from './Pages/Gel/Dashboard.vue';
import GelClients from './Pages/Gel/Clients/Index.vue';
import GelClientShow from './Pages/Gel/Clients/Show.vue';
import GelPoles from './Pages/Gel/Poles/Index.vue';
import GelPoleShow from './Pages/Gel/Poles/Show.vue';
import GelMissions from './Pages/Gel/Missions/Index.vue';
import GelMissionForm from './Pages/Gel/Missions/Form.vue';
import GelDossiers from './Pages/Gel/Dossiers/Explorer.vue';

// Services & Accounting Components
import GelServices from './Pages/Gel/Services/Index.vue';
import GelServicesShow from './Pages/Gel/Services/Show.vue';
import GelAccountingAccounts from './Pages/Gel/Accounting/Accounts.vue';
import GelAccountingJournals from './Pages/Gel/Accounting/JournalList.vue';
import GelAccountingJournalForm from './Pages/Gel/Accounting/JournalForm.vue';
import GelAccountingBalance from './Pages/Gel/Accounting/Balance.vue';
import GelAccountingLedger from './Pages/Gel/Accounting/GeneralLedger.vue';
import GelAccountingBilan from './Pages/Gel/Accounting/Bilan.vue';
import GelAccountingResultat from './Pages/Gel/Accounting/Resultat.vue';

// ERP Components
import ErpCatalogue from './Pages/Gel/Erp/Catalogue.vue';
import ErpStock from './Pages/Gel/Erp/Stock.vue';
import ErpInvoice from './Pages/Gel/Erp/Invoice.vue';
import ErpHr from './Pages/Gel/Erp/Hr.vue';
import ErpTreasury from './Pages/Gel/Erp/Treasury.vue';

// Licenses, Company Admins, Requests
import GelLicenses from './Pages/Gel/Licenses/Index.vue';
import GelCompanyAdmins from './Pages/Gel/CompanyAdmins/Index.vue';
import GelRequests from './Pages/Gel/Admin/Requests/Index.vue';
import GelSettings from './Pages/Gel/Settings.vue';

const app = createApp({});

// Global formatting helpers
app.config.globalProperties.$formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

app.config.globalProperties.$formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR');
};

app.component('GelLayout', GelLayout);

app.component('GelDashboard', GelDashboard);
app.component('GelClients', GelClients);
app.component('GelClientShow', GelClientShow);
app.component('GelPoles', GelPoles);
app.component('GelPoleShow', GelPoleShow);
app.component('GelMissions', GelMissions);
app.component('GelMissionForm', GelMissionForm);
app.component('GelDossiers', GelDossiers);

app.component('GelServices', GelServices);
app.component('GelServicesShow', GelServicesShow);
app.component('GelAccountingAccounts', GelAccountingAccounts);
app.component('GelAccountingJournals', GelAccountingJournals);
app.component('GelAccountingJournalForm', GelAccountingJournalForm);
app.component('GelAccountingBalance', GelAccountingBalance);
app.component('GelAccountingLedger', GelAccountingLedger);
app.component('GelAccountingBilan', GelAccountingBilan);
app.component('GelAccountingResultat', GelAccountingResultat);

// ERP
app.component('ErpCatalogue', ErpCatalogue);
app.component('ErpStock', ErpStock);
app.component('ErpInvoice', ErpInvoice);
app.component('ErpHr', ErpHr);
app.component('ErpTreasury', ErpTreasury);

// Licenses, Company Admins, Requests
app.component('GelLicenses', GelLicenses);
app.component('GelCompanyAdmins', GelCompanyAdmins);
app.component('GelRequests', GelRequests);
app.component('GelSettings', GelSettings);

// Nos Services Components
import PublicCatalogueIndex from './Pages/Public/Catalogue/Index.vue';
import PublicCatalogueShow from './Pages/Public/Catalogue/Show.vue';
import PublicOrderWizard from './Pages/Public/Catalogue/OrderWizard.vue';
import ClientOrdersIndex from './Pages/Client/Orders/Index.vue';
import ClientOrdersShow from './Pages/Client/Orders/Show.vue';
import AdminOrdersKanban from './Pages/Admin/Catalogue/Orders/Kanban.vue';
import AdminOrdersArchives from './Pages/Admin/Catalogue/Orders/Archives.vue';
import AdminOrdersShow from './Pages/Admin/Catalogue/Orders/Show.vue';
import AdminServicesIndex from './Pages/Admin/Catalogue/Services/Index.vue';

app.component('public-catalogue-index', PublicCatalogueIndex);
app.component('public-catalogue-show', PublicCatalogueShow);
app.component('public-order-wizard', PublicOrderWizard);
app.component('client-orders-index', ClientOrdersIndex);
app.component('client-orders-show', ClientOrdersShow);
app.component('admin-orders-kanban', AdminOrdersKanban);
app.component('admin-orders-archives', AdminOrdersArchives);
app.component('admin-orders-show', AdminOrdersShow);
app.component('admin-services-index', AdminServicesIndex);


// Company Portal Components
import CompanyDashboard from './Pages/Company/Dashboard.vue';
import CompanyServices from './Pages/Company/Services.vue';
import CompanyProfile from './Pages/Company/Profile.vue';
import CompanyUsers from './Pages/Company/Users.vue';
import CompanyGed from './Pages/Company/Ged.vue';
import CompanyInvoices from './Pages/Company/Invoices.vue';
import CompanyHumanResources from './Pages/Company/HumanResources.vue';
import CompanyNotifications from './Pages/Company/Notifications.vue';
import CompanyLegal from './Pages/Company/Legal.vue';
import CompanyProjects from './Pages/Company/Projects.vue';
import CompanyCrm from './Pages/Company/Crm.vue';
import CompanyAiAssistant from './Pages/Company/AiAssistant.vue';
import CompanyAccounting from './Pages/Company/Accounting.vue';
import CompanyCaisse from './Pages/Company/Caisse.vue';

app.component('CompanyDashboard', CompanyDashboard);
app.component('CompanyServices', CompanyServices);
app.component('CompanyProfile', CompanyProfile);
app.component('CompanyUsers', CompanyUsers);
app.component('CompanyGed', CompanyGed);
app.component('CompanyInvoices', CompanyInvoices);
app.component('CompanyHumanResources', CompanyHumanResources);
app.component('CompanyNotifications', CompanyNotifications);
app.component('CompanyLegal', CompanyLegal);
app.component('CompanyProjects', CompanyProjects);
app.component('CompanyCrm', CompanyCrm);
app.component('CompanyAiAssistant', CompanyAiAssistant);
app.component('CompanyAccounting', CompanyAccounting);
app.component('CompanyCaisse', CompanyCaisse);

// CPA (Crescendo) Dashboard Components
import CpaDashboard from './Pages/Cpa/Dashboard.vue';
import CpaLogin from './Pages/Auth/CpaLogin.vue';
import CpaRegister from './Pages/Auth/CpaRegister.vue';
app.component('cpa-dashboard', CpaDashboard);
app.component('cpa-login', CpaLogin);
app.component('cpa-register', CpaRegister);

// Only mount Vue on pages that have the #app container
if (document.getElementById('app')) {
    app.mount('#app');
}
