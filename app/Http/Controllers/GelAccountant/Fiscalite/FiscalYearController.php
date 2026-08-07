<?php

namespace App\Http\Controllers\GelAccountant\Fiscalite;

use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use App\Models\AccountingJournal;
use App\Models\AccountingAccount;
use App\Models\AccountingJournalLine;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FiscalYearController extends Controller
{
    /**
     * Liste des exercices fiscaux.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $years = FiscalYear::where('client_id', $clientId)
            ->with('closedBy')
            ->orderBy('year', 'desc')
            ->get();

        return view('gel-accountant.fiscalite.exercices.index', compact('years') + [
            'currentSection' => 'fiscalite',
            'currentPage' => 'exercices'
        ]);
    }

    /**
     * Clôture définitive d'un exercice et génération des À Nouveaux.
     */
    public function close(Request $request, $id)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $fy = FiscalYear::where('client_id', $clientId)->where('status', 'open')->findOrFail($id);

        $request->validate([
            'confirm_close' => 'required|accepted'
        ]);

        DB::transaction(function () use ($fy, $clientId, $user) {
            // 1. Marquer l'exercice comme clôturé (verrouillé)
            $fy->update([
                'status'               => 'closed', // ou 'locked'
                'closed_at'            => now(),
                'closed_by'            => $user->id,
                'check_balance'        => true,
                'check_tva'            => true,
                'check_cnss'           => true,
                'check_reconciliation' => true,
                'check_inventory'      => true,
            ]);

            // 2. Créer l'exercice suivant s'il n'existe pas
            $nextYearNumber = $fy->year + 1;
            $nextFy = FiscalYear::firstOrCreate([
                'client_id' => $clientId,
                'year'      => $nextYearNumber,
            ], [
                'date_start' => $nextYearNumber . '-01-01',
                'date_end'   => $nextYearNumber . '-12-31',
                'status'     => 'open',
            ]);

            // 3. Calculer les soldes des comptes de Bilan (Classes 1 à 5)
            $accounts = AccountingAccount::where('client_id', $clientId)
                ->whereIn('syscohada_class', ['1', '2', '3', '4', '5'])
                ->get();

            $anLines = [];
            foreach ($accounts as $account) {
                $linesQuery = $account->journalLines()->whereHas('journal', function ($q) use ($fy) {
                    $q->where('fiscal_year_id', $fy->id)->where('status', 'posted');
                });
                
                $debit = (float) $linesQuery->sum('debit');
                $credit = (float) $linesQuery->sum('credit');
                $solde = $debit - $credit;

                if ($solde != 0) {
                    if ($solde > 0) {
                        $anLines[] = [
                            'account_id' => $account->id,
                            'label'      => 'À Nouveaux ' . $nextYearNumber,
                            'debit'      => $solde,
                            'credit'     => 0,
                        ];
                    } else {
                        $anLines[] = [
                            'account_id' => $account->id,
                            'label'      => 'À Nouveaux ' . $nextYearNumber,
                            'debit'      => 0,
                            'credit'     => abs($solde),
                        ];
                    }
                }
            }

            // 4. Générer l'écriture d'À Nouveaux si des soldes existent
            if (count($anLines) > 0) {
                $journal = AccountingJournal::create([
                    'client_id'      => $clientId,
                    'journal_type'   => 'anouveaux',
                    'entry_date'     => $nextFy->date_start,
                    'reference'      => 'AN-' . $nextYearNumber,
                    'description'    => 'Génération automatique des À Nouveaux',
                    'status'         => 'posted', // Les AN sont postés directement
                    'created_by'     => $user->id,
                    'validated_by'   => $user->id,
                    'validated_at'   => now(),
                    'fiscal_year_id' => $nextFy->id,
                ]);

                foreach ($anLines as $lineData) {
                    $journal->lines()->create($lineData);
                }
            }

            AuditTrailService::log($fy, 'closed', null, $fy->toArray(), 'Clôture définitive par l\'Expert-Comptable et génération des AN');
        });

        return redirect()->route('gel-accountant.fiscalite.exercices.index')->with('success', 'L\'exercice a été clôturé avec succès et les écritures d\'À Nouveaux ont été générées pour l\'année suivante.');
    }
}
