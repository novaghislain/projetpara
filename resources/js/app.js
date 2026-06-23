// =============================================================================
// FICHIER : app.js
// RÔLE    : Point d'entrée du bundle JavaScript — Initialise Vue 3
// ÉQUIPE  : GEL Cabinet — Équipe Dev Frontend
// =============================================================================
// Ce fichier est compilé par Vite (via resources/js/app.js → vite.config.js).
// Il importe tous les composants Vue 3, les layouts, et les stores, puis
// monte l'application sur l'élément #app du DOM.
//
// Architecture des imports :
//   1. CSS (Tailwind + Bootstrap personnalisé)
//   2. Bootstrap JS (Sidebar, Offcanvas, etc.)
//   3. Stores (Authentication)
//   4. Layouts (GelLayout, CompanyLayout, CpaLayout, ClientLayout)
//   5. Pages (Gel/, Company/, Client/, Modules/, Auth/)
//   6. Composants (Omnisearch, Modals, etc.)
//
// ⚠️  Ne JAMAIS importer de fichier qui n'est pas utilisé — Vite tree-shake
//     les imports, mais l'import lui-même peut avoir des side effects.
// =============================================================================

import '../css/app.css';
import '../css/company.css';
import './bootstrap';

import { createApp } from 'vue';
import Root from './Root.vue';
import { initAuth } from './stores/auth';

// Initialise l'état d'authentification depuis les données JSON injectées
// par Laravel dans Blade (avant que Vue ne monte)
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
import GelAccountingBudgets from './Pages/Gel/Accounting/Budgets/Index.vue';
import GelAccountingTaxDeclarations from './Pages/Gel/Accounting/TaxDeclarations/Index.vue';
import GelAccountingClosing from './Pages/Gel/Accounting/Closing/Index.vue';
import GelAccountingDashboard from './Pages/Gel/Accounting/Dashboard.vue';

// ERP Components
import ErpCatalogue from './Pages/Gel/Erp/Catalogue.vue';
import ErpStock from './Pages/Gel/Erp/Stock.vue';
import ErpInvoice from './Pages/Gel/Erp/Invoice.vue';
import ErpTreasury from './Pages/Gel/Erp/Treasury.vue';

// Commerce / POS Components
import CommerceDashboard from './Pages/Commerce/Dashboard.vue';
import CommerceProducts from './Pages/Commerce/Products.vue';
import CommerceCategories from './Pages/Commerce/Categories.vue';
import CommerceSuppliers from './Pages/Commerce/Suppliers.vue';
import CommercePos from './Pages/Commerce/PosPage.vue';
import CommerceInventory from './Pages/Commerce/Inventory.vue';
import CommerceBusinessUsers from './Pages/Commerce/BusinessUsers.vue';

// Onboarding / Inscription Components
import OnboardingStep1 from './Pages/Onboarding/Step1_Company.vue';
import OnboardingStep2 from './Pages/Onboarding/Step2_Domain.vue';
import OnboardingStep3 from './Pages/Onboarding/Step3_Plan.vue';
import OnboardingStep4 from './Pages/Onboarding/Step4_Admin.vue';
import OnboardingStep5 from './Pages/Onboarding/Step5_Confirm.vue';

// Licenses, Company Admins, Requests
import GelPersonnel from './Pages/Gel/Personnel/Index.vue';
import GelLicenses from './Pages/Gel/Licenses/Index.vue';
import GelCompanyAdmins from './Pages/Gel/CompanyAdmins/Index.vue';
import GelRequests from './Pages/Gel/Admin/Requests/Index.vue';
import GelSettings from './Pages/Gel/Settings.vue';

const app = createApp(Root);

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
app.component('GelAccountingBudgets', GelAccountingBudgets);
app.component('GelAccountingTaxDeclarations', GelAccountingTaxDeclarations);
app.component('GelAccountingClosing', GelAccountingClosing);
app.component('GelAccounting', GelAccountingDashboard);

// ERP
app.component('ErpCatalogue', ErpCatalogue);
app.component('ErpStock', ErpStock);
app.component('ErpInvoice', ErpInvoice);
app.component('ErpTreasury', ErpTreasury);

// Licenses, Company Admins, Requests
app.component('GelLicenses', GelLicenses);
app.component('GelPersonnel', GelPersonnel);
app.component('GelCompanyAdmins', GelCompanyAdmins);
app.component('GelRequests', GelRequests);
app.component('GelSettings', GelSettings);

// Commerce / POS
app.component('commerce-dashboard', CommerceDashboard);
app.component('commerce-products', CommerceProducts);
app.component('commerce-categories', CommerceCategories);
app.component('commerce-suppliers', CommerceSuppliers);
app.component('commerce-pos', CommercePos);
app.component('commerce-inventory', CommerceInventory);
app.component('commerce-business-users', CommerceBusinessUsers);

// Onboarding
app.component('onboarding-step1', OnboardingStep1);
app.component('onboarding-step2', OnboardingStep2);
app.component('onboarding-step3', OnboardingStep3);
app.component('onboarding-step4', OnboardingStep4);
app.component('onboarding-step5', OnboardingStep5);

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
import CompanyEmecef from './Pages/Company/Emecef.vue';

app.component('company-dashboard', CompanyDashboard);
app.component('company-services', CompanyServices);
app.component('company-profile', CompanyProfile);
app.component('company-users', CompanyUsers);
app.component('company-ged', CompanyGed);
app.component('company-invoices', CompanyInvoices);
app.component('company-human-resources', CompanyHumanResources);
app.component('company-notifications', CompanyNotifications);
app.component('company-legal', CompanyLegal);
app.component('company-projects', CompanyProjects);
app.component('company-crm', CompanyCrm);
app.component('company-ai-assistant', CompanyAiAssistant);
app.component('company-accounting', CompanyAccounting);
app.component('company-caisse', CompanyCaisse);
app.component('company-emecef', CompanyEmecef);

// CPA (Crescendo) Dashboard Components
import CpaDashboard from './Pages/Cpa/Dashboard.vue';
import CpaLogin from './Pages/Auth/CpaLogin.vue';
import CpaRegister from './Pages/Auth/CpaRegister.vue';
import CpaTest from './Pages/Cpa/Test.vue';
app.component('cpa-dashboard', CpaDashboard);
app.component('cpa-login', CpaLogin);
app.component('cpa-register', CpaRegister);
app.component('cpa-test', CpaTest);

// ─── DAE Module Components ─────────────────────────────
import DaeDashboard from './Pages/Modules/Dae/Dashboard.vue';
import DaeCourriersIndex from './Pages/Modules/Dae/Courriers/Index.vue';
import DaeCourriersForm from './Pages/Modules/Dae/Courriers/Form.vue';
import DaeCourriersShow from './Pages/Modules/Dae/Courriers/Show.vue';
import DaeEmailsIndex from './Pages/Modules/Dae/Emails/Index.vue';
import DaeAgendaIndex from './Pages/Modules/Dae/Agenda/Index.vue';
import DaeContratsIndex from './Pages/Modules/Dae/Contrats/Index.vue';
import DaeContratsForm from './Pages/Modules/Dae/Contrats/Form.vue';
import DaeContratsShow from './Pages/Modules/Dae/Contrats/Show.vue';
import DaeDocumentsIndex from './Pages/Modules/Dae/Documents/Index.vue';
import DaeDocumentsShow from './Pages/Modules/Dae/Documents/Show.vue';
import DaeConformiteIndex from './Pages/Modules/Dae/Conformite/Index.vue';
import DaeConformiteShow from './Pages/Modules/Dae/Conformite/Show.vue';
import DaeRapportsIndex from './Pages/Modules/Dae/Rapports/Index.vue';
import DaeRapportsShow from './Pages/Modules/Dae/Rapports/Show.vue';
import DaeTachesIndex from './Pages/Modules/Dae/Taches/Index.vue';
import DaeModelesIndex from './Pages/Modules/Dae/Modeles/Index.vue';
import DaePersonnelIndex from './Pages/Modules/Dae/Personnel/Index.vue';
import DaePersonnelShow from './Pages/Modules/Dae/Personnel/Show.vue';

app.component('dae-dashboard', DaeDashboard);
app.component('dae-courriers-index', DaeCourriersIndex);
app.component('dae-courriers-form', DaeCourriersForm);
app.component('dae-courriers-show', DaeCourriersShow);
app.component('dae-emails-index', DaeEmailsIndex);
app.component('dae-agenda-index', DaeAgendaIndex);
app.component('dae-contrats-index', DaeContratsIndex);
app.component('dae-contrats-form', DaeContratsForm);
app.component('dae-contrats-show', DaeContratsShow);
app.component('dae-documents-index', DaeDocumentsIndex);
app.component('dae-documents-show', DaeDocumentsShow);
app.component('dae-conformite-index', DaeConformiteIndex);
app.component('dae-conformite-show', DaeConformiteShow);
app.component('dae-rapports-index', DaeRapportsIndex);
app.component('dae-rapports-show', DaeRapportsShow);
app.component('dae-taches-index', DaeTachesIndex);
app.component('dae-modeles-index', DaeModelesIndex);
app.component('dae-personnel-index', DaePersonnelIndex);
app.component('dae-personnel-show', DaePersonnelShow);

// Company DAE
import CompanyDaeDashboard from './Pages/Company/DaeDashboard.vue';
app.component('company-dae-dashboard', CompanyDaeDashboard);

import CompanyDaeCourriers from './Pages/Company/CompanyDaeCourriers.vue';
app.component('company-dae-courriers', CompanyDaeCourriers);

import CompanyDaeContrats from './Pages/Company/CompanyDaeContrats.vue';
app.component('company-dae-contrats', CompanyDaeContrats);

import CompanyDaeDocuments from './Pages/Company/CompanyDaeDocuments.vue';
app.component('company-dae-documents', CompanyDaeDocuments);

import CompanyDaeTaches from './Pages/Company/CompanyDaeTaches.vue';
app.component('company-dae-taches', CompanyDaeTaches);

// ─── Legal Module Components ───────────────────────────
import LegalDashboard from './Pages/Modules/Legal/Dashboard.vue';
import LegalSociete from './Pages/Modules/Legal/Societe/Index.vue';
import LegalAssembleesIndex from './Pages/Modules/Legal/Assemblees/Index.vue';
import LegalAssembleesForm from './Pages/Modules/Legal/Assemblees/Form.vue';
import LegalAssembleesShow from './Pages/Modules/Legal/Assemblees/Show.vue';
import LegalContratsIndex from './Pages/Modules/Legal/Contrats/Index.vue';
import LegalContratsForm from './Pages/Modules/Legal/Contrats/Form.vue';
import LegalContratsShow from './Pages/Modules/Legal/Contrats/Show.vue';
import LegalContentieuxIndex from './Pages/Modules/Legal/Contentieux/Index.vue';
import LegalContentieuxForm from './Pages/Modules/Legal/Contentieux/Form.vue';
import LegalContentieuxShow from './Pages/Modules/Legal/Contentieux/Show.vue';
import LegalConformiteIndex from './Pages/Modules/Legal/Conformite/Index.vue';
import LegalConformiteForm from './Pages/Modules/Legal/Conformite/Form.vue';
import LegalConformiteCalendrier from './Pages/Modules/Legal/Conformite/Calendrier.vue';
import LegalBibliothequeIndex from './Pages/Modules/Legal/Bibliotheque/Index.vue';
import LegalBibliothequeForm from './Pages/Modules/Legal/Bibliotheque/Form.vue';
import LegalBibliothequeGenerer from './Pages/Modules/Legal/Bibliotheque/Generer.vue';
import LegalRegistresShow from './Pages/Modules/Legal/Registres/Show.vue';
import LegalDossiersIndex from './Pages/Modules/Legal/Dossiers/Index.vue';
import LegalDossiersForm from './Pages/Modules/Legal/Dossiers/Form.vue';
import LegalDossiersShow from './Pages/Modules/Legal/Dossiers/Show.vue';

app.component('legal-dashboard', LegalDashboard);
app.component('legal-societe', LegalSociete);
app.component('legal-assemblees-index', LegalAssembleesIndex);
app.component('legal-assemblees-form', LegalAssembleesForm);
app.component('legal-assemblees-show', LegalAssembleesShow);
app.component('legal-contrats-index', LegalContratsIndex);
app.component('legal-contrats-form', LegalContratsForm);
app.component('legal-contrats-show', LegalContratsShow);
app.component('legal-contentieux-index', LegalContentieuxIndex);
app.component('legal-contentieux-form', LegalContentieuxForm);
app.component('legal-contentieux-show', LegalContentieuxShow);
app.component('legal-conformite-index', LegalConformiteIndex);
app.component('legal-conformite-form', LegalConformiteForm);
app.component('legal-conformite-calendrier', LegalConformiteCalendrier);
app.component('legal-bibliotheque-index', LegalBibliothequeIndex);
app.component('legal-bibliotheque-form', LegalBibliothequeForm);
app.component('legal-bibliotheque-generer', LegalBibliothequeGenerer);
app.component('legal-registres-show', LegalRegistresShow);
app.component('legal-dossiers-index', LegalDossiersIndex);
app.component('legal-dossiers-form', LegalDossiersForm);
app.component('legal-dossiers-show', LegalDossiersShow);

// ─── RH Module Components ──────────────────────────────
import RhDashboard from './Pages/Modules/Rh/Dashboard.vue';
import RhEmployeesIndex from './Pages/Modules/Rh/Employees/Index.vue';
import RhEmployeesForm from './Pages/Modules/Rh/Employees/Form.vue';
import RhEmployeesShow from './Pages/Modules/Rh/Employees/Show.vue';
import RhContractsIndex from './Pages/Modules/Rh/Contracts/Index.vue';
import RhLeavesIndex from './Pages/Modules/Rh/Leaves/Index.vue';
import RhExpensesIndex from './Pages/Modules/Rh/Expenses/Index.vue';
import RhPayrollsIndex from './Pages/Modules/Rh/Payrolls/Index.vue';
import RhPayrollsShow from './Pages/Modules/Rh/Payrolls/Show.vue';
import RhAttendanceIndex from './Pages/Modules/Rh/Attendance/Index.vue';
import RhTrainingsIndex from './Pages/Modules/Rh/Trainings/Index.vue';
import RhAlertsIndex from './Pages/Modules/Rh/Alerts/Index.vue';

app.component('rh-dashboard', RhDashboard);
app.component('rh-employees-index', RhEmployeesIndex);
app.component('rh-employees-form', RhEmployeesForm);
app.component('rh-employees-show', RhEmployeesShow);
app.component('rh-contracts-index', RhContractsIndex);
app.component('rh-leaves-index', RhLeavesIndex);
app.component('rh-expenses-index', RhExpensesIndex);
app.component('rh-payrolls-index', RhPayrollsIndex);
app.component('rh-payrolls-show', RhPayrollsShow);
app.component('rh-attendance-index', RhAttendanceIndex);
app.component('rh-trainings-index', RhTrainingsIndex);
app.component('rh-alerts-index', RhAlertsIndex);

// Company RH
import CompanyRhDashboard from './Pages/Company/Rh/Dashboard.vue';
import CompanyRhEmployees from './Pages/Company/Rh/Employees.vue';
import CompanyRhLeaves from './Pages/Company/Rh/Leaves.vue';
import CompanyRhExpenses from './Pages/Company/Rh/Expenses.vue';
import CompanyRhPayrolls from './Pages/Company/Rh/Payrolls.vue';
import CompanyRhTrainings from './Pages/Company/Rh/Trainings.vue';

app.component('company-rh-dashboard', CompanyRhDashboard);
app.component('company-rh-employees', CompanyRhEmployees);
app.component('company-rh-leaves', CompanyRhLeaves);
app.component('company-rh-expenses', CompanyRhExpenses);
app.component('company-rh-payrolls', CompanyRhPayrolls);
app.component('company-rh-trainings', CompanyRhTrainings);

// ─── IT — Helpdesk & Ticketing Components ──────────────────
import GelItTickets from './Pages/Gel/It/Tickets/Index.vue';
import GelItTicketsForm from './Pages/Gel/It/Tickets/Form.vue';
import GelItTicketsShow from './Pages/Gel/It/Tickets/Show.vue';
import GelItAssets from './Pages/Gel/It/Assets/Index.vue';
import GelItAssetsForm from './Pages/Gel/It/Assets/Form.vue';
import GelItAssetsShow from './Pages/Gel/It/Assets/Show.vue';
import GelItSlaPolicies from './Pages/Gel/It/SlaPolicies/Index.vue';
import GelItSlaPoliciesForm from './Pages/Gel/It/SlaPolicies/Form.vue';
import GelItSlaPoliciesShow from './Pages/Gel/It/SlaPolicies/Show.vue';
import GelItMaintenanceContracts from './Pages/Gel/It/MaintenanceContracts/Index.vue';
import GelItMaintenanceContractsForm from './Pages/Gel/It/MaintenanceContracts/Form.vue';
import GelItMaintenanceContractsShow from './Pages/Gel/It/MaintenanceContracts/Show.vue';
import GelItKnowledgeBase from './Pages/Gel/It/KnowledgeBase/Index.vue';
import GelItKnowledgeBaseForm from './Pages/Gel/It/KnowledgeBase/Form.vue';
import GelItKnowledgeBaseShow from './Pages/Gel/It/KnowledgeBase/Show.vue';

app.component('gel-it-tickets', GelItTickets);
app.component('gel-it-tickets-form', GelItTicketsForm);
app.component('gel-it-tickets-show', GelItTicketsShow);
app.component('gel-it-assets', GelItAssets);
app.component('gel-it-assets-form', GelItAssetsForm);
app.component('gel-it-assets-show', GelItAssetsShow);
app.component('gel-it-sla-policies', GelItSlaPolicies);
app.component('gel-it-sla-policies-form', GelItSlaPoliciesForm);
app.component('gel-it-sla-policies-show', GelItSlaPoliciesShow);
app.component('gel-it-maintenance-contracts', GelItMaintenanceContracts);
app.component('gel-it-maintenance-contracts-form', GelItMaintenanceContractsForm);
app.component('gel-it-maintenance-contracts-show', GelItMaintenanceContractsShow);
app.component('gel-it-knowledge-base', GelItKnowledgeBase);
app.component('gel-it-knowledge-base-form', GelItKnowledgeBaseForm);
app.component('gel-it-knowledge-base-show', GelItKnowledgeBaseShow);

// ─── Tontines Components ─────────────────────────────────
import GelTontines from './Pages/Gel/Tontines/Index.vue';
import GelTontinesForm from './Pages/Gel/Tontines/Form.vue';
import GelTontinesShow from './Pages/Gel/Tontines/Show.vue';

app.component('gel-tontines', GelTontines);
app.component('gel-tontines-form', GelTontinesForm);
app.component('gel-tontines-show', GelTontinesShow);

// ─── Agents IA Components ──────────────────────────────
import AiAgents from './Pages/Gel/Ai/Agents.vue';
app.component('ai-agents', AiAgents);

// ─── Télédéclaration Components ──────────────────────────
import GelTeleDeclarations from './Pages/Gel/TeleDeclarations/Index.vue';
import GelTeleDeclarationsForm from './Pages/Gel/TeleDeclarations/Form.vue';
import GelTeleDeclarationsShow from './Pages/Gel/TeleDeclarations/Show.vue';

app.component('gel-tele-declarations', GelTeleDeclarations);
app.component('gel-tele-declarations-form', GelTeleDeclarationsForm);
app.component('gel-tele-declarations-show', GelTeleDeclarationsShow);

// ─── Signatures électroniques Components ────────────────
import GelDocumentSignatures from './Pages/Gel/DocumentSignatures/Index.vue';
import GelDocumentSignaturesForm from './Pages/Gel/DocumentSignatures/Form.vue';
import GelDocumentSignaturesShow from './Pages/Gel/DocumentSignatures/Show.vue';
import GelDocumentSign from './Pages/Gel/DocumentSignatures/Sign.vue';

app.component('gel-document-signatures', GelDocumentSignatures);
app.component('gel-document-signatures-form', GelDocumentSignaturesForm);
app.component('gel-document-signatures-show', GelDocumentSignaturesShow);
app.component('gel-document-sign', GelDocumentSign);

// ─── Workflows d'approbation Components ─────────────────
import GelApprovalWorkflows from './Pages/Gel/ApprovalWorkflows/Index.vue';
import GelApprovalWorkflowsForm from './Pages/Gel/ApprovalWorkflows/Form.vue';
import GelApprovalWorkflowsShow from './Pages/Gel/ApprovalWorkflows/Show.vue';

app.component('gel-approval-workflows', GelApprovalWorkflows);
app.component('gel-approval-workflows-form', GelApprovalWorkflowsForm);
app.component('gel-approval-workflows-show', GelApprovalWorkflowsShow);

// ─── Articles (Blog) Components ──────────────────────────
import GelArticles from './Pages/Gel/Articles/Index.vue';
import GelArticlesForm from './Pages/Gel/Articles/Form.vue';
import GelArticlesShow from './Pages/Gel/Articles/Show.vue';

app.component('gel-articles', GelArticles);
app.component('gel-articles-form', GelArticlesForm);
app.component('gel-articles-show', GelArticlesShow);

// ─── Règles de relance Components ──────────────────────
import GelRelanceRules from './Pages/Gel/RelanceRules/Index.vue';
import GelRelanceRulesForm from './Pages/Gel/RelanceRules/Form.vue';
import GelRelanceRulesShow from './Pages/Gel/RelanceRules/Show.vue';

app.component('gel-relance-rules', GelRelanceRules);
app.component('gel-relance-rules-form', GelRelanceRulesForm);
app.component('gel-relance-rules-show', GelRelanceRulesShow);

// ─── Centres de coût Components ─────────────────────────
import GelCostCenters from './Pages/Gel/CostCenters/Index.vue';
import GelCostCentersForm from './Pages/Gel/CostCenters/Form.vue';
import GelCostCentersShow from './Pages/Gel/CostCenters/Show.vue';

app.component('gel-cost-centers', GelCostCenters);
app.component('gel-cost-centers-form', GelCostCentersForm);
app.component('gel-cost-centers-show', GelCostCentersShow);

// ─── OCR Components ─────────────────────────────────────
import GelOcr from './Pages/Gel/Ocr/Index.vue';
import GelOcrForm from './Pages/Gel/Ocr/Form.vue';
import GelOcrShow from './Pages/Gel/Ocr/Show.vue';

app.component('gel-ocr', GelOcr);
app.component('gel-ocr-form', GelOcrForm);
app.component('gel-ocr-show', GelOcrShow);

// ─── Paie — Calculateur IRPP/CNSS ──────────────────────
import GelPaie from './Pages/Gel/Paie/Index.vue';
app.component('gel-paie', GelPaie);

// ─── Sécurité — 2FA & Sessions ─────────────────────────
import GelSecurity from './Pages/Gel/Security/Index.vue';
app.component('gel-security', GelSecurity);

// ─── Audit — Journal d'Audit ──────────────────────────
import GelAudit from './Pages/Gel/Audit/Index.vue';
app.component('gel-audit', GelAudit);

// ─── IA & Automatisation Components ────────────────────
import GelAiFeed from './Pages/Gel/Ai/Feed.vue';
import GelAiReconciliation from './Pages/Gel/Ai/Reconciliation.vue';
import GelAiRelances from './Pages/Gel/Ai/Relances.vue';
import GelAiOcr from './Pages/Gel/Ai/Ocr.vue';
import GelAiCashflow from './Pages/Gel/Ai/Cashflow.vue';

app.component('gel-ai-feed', GelAiFeed);
app.component('gel-ai-reconciliation', GelAiReconciliation);
app.component('gel-ai-relances', GelAiRelances);
app.component('gel-ai-ocr', GelAiOcr);
app.component('gel-ai-cashflow', GelAiCashflow);

// ─── Multi-Tenant / Permissions Components ────────────
import SelectContext from './Pages/Company/SelectContext.vue';
import GelClientModules from './Pages/Gel/Clients/Modules.vue';
import CompanyUserPermissions from './Pages/Company/UserPermissions.vue';

app.component('select-context', SelectContext);
app.component('gel-client-modules', GelClientModules);
app.component('company-user-permissions', CompanyUserPermissions);

// Mount Vue — passes page name and props from Blade data attributes
const appEl = document.getElementById('app');
if (appEl) {
    const page = appEl.dataset.page || '';
    let props = {};
    try {
        const raw = appEl.dataset.props || '{}';
        props = JSON.parse(raw);
    } catch (e) {
        console.warn('Failed to parse page props:', e);
    }
    app.provide('page', page);
    app.provide('pageProps', props);
    app.mount('#app');
}
