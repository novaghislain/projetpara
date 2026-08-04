<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AccountingBudget;
use App\Models\AccountingBudgetLine;
use App\Models\AccountingClosingEntry;
use App\Models\AccountingTaxDeclaration;
use App\Models\FiscalYear;
use App\Services\Accounting\ClosingService;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\TaxCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de comptabilité avancée (Company).
 *
 * Gère la budgétisation (budgets prévisionnels), les déclarations fiscales
 * (TVA, IS, ITS, CNSS, VPS) avec calculs automatiques, et les opérations
 * de clôture / réouverture d'exercice comptable.
 *
 * Utilise les services TaxCalculationService, ClosingService et JournalService.
 */
class CompanyAccountingController extends BaseCompanyController
{
    protected TaxCalculationService $taxService;
    protected ClosingService $closingService;
    protected JournalService $journalService;

    /**
     * Constructeur : injecte les services de calcul fiscal, clôture et journal.
     *
     * @param TaxCalculationService $taxService Service de calcul des taxes
     * @param ClosingService $closingService Service de clôture d'exercice
     * @param JournalService $journalService Service de gestion des journaux
     */
    public function __construct(
        TaxCalculationService $taxService,
        ClosingService $closingService,
        JournalService $journalService,
    ) {
        $this->taxService = $taxService;
        $this->closingService = $closingService;
        $this->journalService = $journalService;
    }

    // ─── Budgets ──────────────────────────────────────────────

    /**
     * API: Liste tous les budgets de l'entreprise.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function budgets()
    {
        $clientId = $this->getClientId();
        return AccountingBudget::where('client_id', $clientId)
            ->with('fiscalYear')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * API: Crée un nouveau budget.
     *
     * @param Request $request Requête HTTP (fiscal_year_id, name, type, montant_prevu, notes)
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBudget(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:recette,depense,tresorerie,investissement',
            'montant_prevu' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['client_id'] = $clientId;
        $validated['status'] = 'brouillon';
        $validated['created_by'] = Auth::id();

        $budget = AccountingBudget::create($validated);
        return response()->json($budget->load('fiscalYear'), 201);
    }

    /**
     * API: Affiche un budget avec ses lignes et comptes.
     *
     * @param int $id Identifiant du budget
     * @return \App\Models\AccountingBudget
     */
    public function showBudget($id)
    {
        $clientId = $this->getClientId();
        return AccountingBudget::where('client_id', $clientId)
            ->with(['fiscalYear', 'lines.account'])
            ->findOrFail($id);
    }

    /**
     * API: Ajoute une ligne à un budget.
     *
     * Met à jour le montant prévu total du budget.
     *
     * @param Request $request Requête HTTP (account_id, label, montant_prevu)
     * @param int $budgetId Identifiant du budget
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBudgetLine(Request $request, $budgetId)
    {
        $budget = AccountingBudget::where('client_id', $this->getClientId())->findOrFail($budgetId);
        $validated = $request->validate([
            'account_id' => 'required|exists:accounting_accounts,id',
            'label' => 'required|string|max:255',
            'montant_prevu' => 'required|numeric|min:0',
        ]);
        $validated['budget_id'] = $budget->id;
        $line = AccountingBudgetLine::create($validated);
        $budget->increment('montant_prevu', $validated['montant_prevu']);
        return response()->json($line->load('account'), 201);
    }

    /**
     * API: Modifie une ligne de budget (montant, libellé).
     *
     * Recalcule le montant prévu total du budget par différence.
     *
     * @param Request $request Requête HTTP (montant_prevu, label)
     * @param int $budgetId Identifiant du budget
     * @param int $lineId Identifiant de la ligne de budget
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBudgetLine(Request $request, $budgetId, $lineId)
    {
        $line = AccountingBudgetLine::where('budget_id', $budgetId)->findOrFail($lineId);
        $old = $line->montant_prevu;
        $line->update($request->validate(['montant_prevu' => 'numeric|min:0', 'label' => 'string|max:255']));
        $diff = ($line->montant_prevu ?? $old) - $old;
        $line->budget->increment('montant_prevu', $diff);
        return response()->json($line->fresh('account'));
    }

    /**
     * API: Supprime une ligne de budget.
     *
     * Décrémente le montant prévu total du budget.
     *
     * @param int $budgetId Identifiant du budget
     * @param int $lineId Identifiant de la ligne à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeBudgetLine($budgetId, $lineId)
    {
        $line = AccountingBudgetLine::where('budget_id', $budgetId)->findOrFail($lineId);
        $line->budget->decrement('montant_prevu', $line->montant_prevu);
        $line->delete();
        return response()->json(['message' => 'Ligne supprimée']);
    }

    /**
     * API: Valide et active un budget (statut "actif").
     *
     * @param int $id Identifiant du budget
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateBudget($id)
    {
        $budget = AccountingBudget::where('client_id', $this->getClientId())->findOrFail($id);
        $budget->update(['status' => 'actif', 'validated_by' => Auth::id(), 'validated_at' => now()]);
        return response()->json($budget);
    }

    /**
     * API: Supprime un budget et ses lignes.
     *
     * @param int $id Identifiant du budget
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyBudget($id)
    {
        $budget = AccountingBudget::where('client_id', $this->getClientId())->findOrFail($id);
        $budget->lines()->delete();
        $budget->delete();
        return response()->json(['message' => 'Budget supprimé']);
    }

    // ─── Déclarations Fiscales ────────────────────────────────

    /**
     * API: Liste les déclarations fiscales du client.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function taxDeclarations()
    {
        $clientId = $this->getClientId();
        return AccountingTaxDeclaration::where('client_id', $clientId)
            ->with('fiscalYear')
            ->orderBy('period_year', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * API: Calcule et génère une déclaration de TVA pour une période mensuelle.
     *
     * Utilise le TaxCalculationService pour déterminer la TVA collectée,
     * la TVA récupérable et le montant net dû.
     *
     * @param Request $request Requête HTTP (fiscal_year_id, month)
     * @return \Illuminate\Http\JsonResponse
     */
    public function computeTva(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'month' => 'required|integer|min:1|max:12',
        ]);
        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerTva($clientId, $validated['fiscal_year_id'], $validated['month']);

        // Période mensuelle pour la déclaration TVA (pas l'exercice entier)
        $periodStart = now()->setYear($fiscalYear->year)->setMonth($validated['month'])->startOfMonth();
        $periodEnd = min(
            $fiscalYear->date_end,
            now()->setYear($fiscalYear->year)->setMonth($validated['month'])->lastOfMonth()
        );

        $declaration = $this->taxService->genererDeclaration(
            $clientId, $validated['fiscal_year_id'], 'tva', 'mensuel',
            $validated['month'], null, $fiscalYear->year, [
                'date_debut' => $periodStart->format('Y-m-d'),
                'date_fin' => $periodEnd->format('Y-m-d'),
                'base_imposable' => $result['base_imposable'],
                'taux' => $result['taux'],
                'montant_dut' => $result['tva_net'],
                'tva_collectee' => $result['tva_collectee'],
                'tva_recuperable' => $result['tva_recuperable'],
                'tva_net' => $result['tva_net'],
                'credit_tva' => $result['credit_tva'],
            ]
        );
        return response()->json($declaration, 201);
    }

    /**
     * API: Calcule et génère une déclaration d'IS (Impôt sur les Sociétés).
     *
     * Calcule le résultat fiscal et l'impôt dû pour l'exercice.
     *
     * @param Request $request Requête HTTP (fiscal_year_id)
     * @return \Illuminate\Http\JsonResponse
     */
    public function computeIs(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate(['fiscal_year_id' => 'required|exists:fiscal_years,id']);
        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerIs($clientId, $validated['fiscal_year_id']);
        $declaration = $this->taxService->genererDeclaration(
            $clientId, $validated['fiscal_year_id'], 'is', 'annuel',
            null, null, $fiscalYear->year, [
                'date_debut' => $fiscalYear->date_start->format('Y-m-d'),
                'date_fin' => $fiscalYear->date_end->format('Y-m-d'),
                'base_imposable' => $result['resultat_fiscal'],
                'taux' => $result['taux'],
                'montant_dut' => $result['solde'],
                'resultat_fiscal' => $result['resultat_fiscal'],
            ]
        );
        return response()->json($declaration, 201);
    }

    /**
     * API: Calcule et génère une déclaration d'ITS (Impôt sur les Traitements et Salaires).
     *
     * Calcule l'impôt par tranche progressive à partir du salaire brut annuel.
     *
     * @param Request $request Requête HTTP (fiscal_year_id, salaire_brut_annuel)
     * @return \Illuminate\Http\JsonResponse
     */
    public function computeIts(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'salaire_brut_annuel' => 'required|numeric|min:0',
        ]);
        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerIts($validated['salaire_brut_annuel']);
        $declaration = $this->taxService->genererDeclaration(
            $clientId, $validated['fiscal_year_id'], 'its', 'annuel',
            null, null, $fiscalYear->year, [
                'date_debut' => $fiscalYear->date_start->format('Y-m-d'),
                'date_fin' => $fiscalYear->date_end->format('Y-m-d'),
                'base_imposable' => $validated['salaire_brut_annuel'],
                'taux' => $result['taux_effectif'],
                'montant_dut' => $result['total_impot'],
                'tranches' => $result['tranches'],
            ]
        );
        return response()->json($declaration, 201);
    }

    /**
     * API: Calcule et génère une déclaration CNSS.
     *
     * Calcule les parts employeur et salarié à partir du salaire brut mensuel.
     *
     * @param Request $request Requête HTTP (fiscal_year_id, salaire_brut_mensuel)
     * @return \Illuminate\Http\JsonResponse
     */
    public function computeCnss(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'salaire_brut_mensuel' => 'required|numeric|min:0',
        ]);
        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerCnss($validated['salaire_brut_mensuel']);
        $declaration = $this->taxService->genererDeclaration(
            $clientId, $validated['fiscal_year_id'], 'cnss', 'mensuel',
            now()->month, null, $fiscalYear->year, [
                'date_debut' => $fiscalYear->date_start->format('Y-m-d'),
                'date_fin' => $fiscalYear->date_end->format('Y-m-d'),
                'base_imposable' => $result['assiette'],
                'montant_dut' => $result['total'],
                'part_employeur' => $result['part_employeur'],
                'part_salarie' => $result['part_salarie'],
            ]
        );
        return response()->json($declaration, 201);
    }

    /**
     * API: Calcule et génère une déclaration VPS (Versement Patronal sur Salaires).
     *
     * @param Request $request Requête HTTP (fiscal_year_id, masse_salariale)
     * @return \Illuminate\Http\JsonResponse
     */
    public function computeVps(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'masse_salariale' => 'required|numeric|min:0',
        ]);
        $fiscalYear = FiscalYear::findOrFail($validated['fiscal_year_id']);
        $result = $this->taxService->calculerVps($validated['masse_salariale']);
        $declaration = $this->taxService->genererDeclaration(
            $clientId, $validated['fiscal_year_id'], 'vps', 'annuel',
            null, null, $fiscalYear->year, [
                'date_debut' => $fiscalYear->date_start->format('Y-m-d'),
                'date_fin' => $fiscalYear->date_end->format('Y-m-d'),
                'base_imposable' => $validated['masse_salariale'],
                'taux' => $result['taux'],
                'montant_dut' => $result['montant'],
            ]
        );
        return response()->json($declaration, 201);
    }

    /**
     * API: Met à jour le statut d'une déclaration fiscale.
     *
     * Si le statut passe à "payé", enregistre automatiquement le montant payé
     * et met le solde à zéro.
     *
     * @param Request $request Requête HTTP (status)
     * @param int $id Identifiant de la déclaration
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTaxStatus(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $declaration = AccountingTaxDeclaration::where('client_id', $clientId)->findOrFail($id);
        $validated = $request->validate(['status' => 'required|in:brouillon,calcule,depose,paye,en_retard']);
        $data = ['status' => $validated['status']];
        if ($validated['status'] === 'paye') {
            $data['montant_paye'] = $declaration->montant_dut;
            $data['solde'] = 0;
        }
        $declaration->update($data);
        return response()->json($declaration);
    }

    /**
     * API: Supprime une déclaration fiscale.
     *
     * @param int $id Identifiant de la déclaration
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyTaxDeclaration($id)
    {
        $declaration = AccountingTaxDeclaration::where('client_id', $this->getClientId())->findOrFail($id);
        $declaration->delete();
        return response()->json(['message' => 'Déclaration supprimée']);
    }

    // ─── Clôture ───────────────────────────────────────────────

    /**
     * API: Liste les écritures de clôture.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function closingEntries()
    {
        $clientId = $this->getClientId();
        return AccountingClosingEntry::where('client_id', $clientId)
            ->with(['fiscalYear', 'createdBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * API: Clôture un exercice comptable.
     *
     * Délègue au ClosingService pour générer les écritures de clôture
     * (résultat, report à nouveau).
     *
     * @param Request $request Requête HTTP (fiscal_year_id)
     * @return \Illuminate\Http\JsonResponse
     */
    public function closeYear(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate(['fiscal_year_id' => 'required|exists:fiscal_years,id']);
        $fiscalYear = FiscalYear::where('client_id', $clientId)->findOrFail($validated['fiscal_year_id']);
        $result = $this->closingService->cloturerExercice($fiscalYear->id, Auth::id());
        return response()->json($result);
    }

    /**
     * API: Rouvre un exercice comptable préalablement clôturé.
     *
     * @param Request $request Requête HTTP (fiscal_year_id)
     * @return \Illuminate\Http\JsonResponse
     */
    public function reopenYear(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate(['fiscal_year_id' => 'required|exists:fiscal_years,id']);
        $fiscalYear = FiscalYear::where('client_id', $clientId)->findOrFail($validated['fiscal_year_id']);
        $result = $this->closingService->rouvrirExercice($fiscalYear->id, Auth::id());
        return response()->json($result);
    }

    /**
     * API: Crée une écriture d'inventaire.
     *
     * @param Request $request Requête HTTP (fiscal_year_id, entries[])
     * @return \Illuminate\Http\JsonResponse
     */
    public function inventoryEntry(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'entries' => 'required|array|min:1',
            'entries.*.account_id' => 'required|exists:accounting_accounts,id',
            'entries.*.label' => 'required|string|max:500',
            'entries.*.debit' => 'nullable|numeric|min:0',
            'entries.*.credit' => 'nullable|numeric|min:0',
        ]);
        $fiscalYear = FiscalYear::where('client_id', $clientId)->findOrFail($validated['fiscal_year_id']);
        $entry = $this->closingService->ecritureInventaire($fiscalYear->id, Auth::id(), $validated['entries']);
        return response()->json($entry, 201);
    }
}
