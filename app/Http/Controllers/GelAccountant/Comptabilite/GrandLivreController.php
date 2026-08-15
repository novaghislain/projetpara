<?php

namespace App\Http\Controllers\GelAccountant\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du Grand Livre comptable.
 *
 * Affiche l'ensemble des lignes d'écritures validées, classées par
 * compte, pour un cabinet donné. Le Grand Livre peut être filtré par
 * client, par compte et par intervalle de dates.
 */
class GrandLivreController extends Controller
{
    /**
     * Affiche le Grand Livre : toutes les lignes d'écritures validées.
     *
     * Les résultats sont filtrés par cabinet et optionnellement par
     * client, compte comptable et période. Seules les écritures
     * validées (valide = true) sont incluses.
     *
     * @param  Request $request La requête avec les filtres optionnels
     *                          (client_id, compte_id, date_from, date_to).
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? session('cabinet_id');

        // Seules les lignes des écritures validées sont visibles dans le Grand Livre
        $query = LigneEcriture::whereHas('ecriture', function ($q) use ($cabinetId) {
            $q->where('cabinet_id', $cabinetId)->where('valide', true);
        })->with(['ecriture.journal', 'compte', 'ecriture.client']);

        // Filtre par client
        if ($clientId = $request->input('client_id')) {
            $query->whereHas('ecriture', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        // Filtre par compte comptable
        if ($compteId = $request->input('compte_id')) {
            $query->where('compte_id', $compteId);
        }

        // Borne inférieure de la période
        if ($dateFrom = $request->input('date_from')) {
            $query->whereHas('ecriture', function ($q) use ($dateFrom) {
                $q->where('date_ecriture', '>=', $dateFrom);
            });
        }

        // Borne supérieure de la période
        if ($dateTo = $request->input('date_to')) {
            $query->whereHas('ecriture', function ($q) use ($dateTo) {
                $q->where('date_ecriture', '<=', $dateTo);
            });
        }

        // On ordonne par compte, puis par date
        $lignes = $query->join('gel_comptes_comptables', 'gel_lignes_ecriture.compte_id', '=', 'gel_comptes_comptables.id')
            ->orderBy('gel_comptes_comptables.code')
            ->orderBy('ecriture_id')
            ->orderBy('gel_lignes_ecriture.id')
            ->select('gel_lignes_ecriture.*') // pour ne pas mélanger les IDs
            ->get();
        
        $lignesGroupees = $lignes->groupBy('compte_id');
        $comptes = CompteComptable::where('cabinet_id', $cabinetId)
            ->where('actif', true)
            ->where('niveau', '>', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'intitule']);
        $clients = Client::where('cabinet_id', $cabinetId)->actif()->get(['id', 'nom_entreprise']);

        return view('gel-accountant.comptabilite.grand-livre.index', compact('lignesGroupees', 'comptes', 'clients') + ['currentSection' => 'comptabilite', 'currentPage' => 'grand-livre']);
    }
    /**
     * Export CSV du Grand Livre pour un compte.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $cabinetId = $user->cabinet_id ?? session('cabinet_id');
        $compteId = $request->input('compte_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $clientId = $request->input('client_id');

        if (!$compteId) {
            return redirect()->back()->with('error', 'Veuillez sélectionner un compte pour exporter.');
        }

        $compte = CompteComptable::where('cabinet_id', $cabinetId)->findOrFail($compteId);

        $query = LigneEcriture::where('compte_id', $compteId)
            ->whereHas('ecriture', function ($q) use ($cabinetId, $dateFrom, $dateTo, $clientId) {
                $q->where('cabinet_id', $cabinetId)->where('valide', true);
                if ($dateFrom) $q->where('date_ecriture', '>=', $dateFrom);
                if ($dateTo) $q->where('date_ecriture', '<=', $dateTo);
                if ($clientId) $q->where('client_id', $clientId);
            })
            ->with('ecriture.journal')
            ->orderBy('id');

        $lignes = $query->get();

        $callback = function () use ($compte, $lignes) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['GRAND LIVRE — ' . $compte->code . ' — ' . $compte->intitule]);
            fputcsv($output, ['Date', 'Numéro', 'Libellé', 'Débit', 'Crédit', 'Solde']);

            $solde = 0;
            foreach ($lignes as $ligne) {
                $montant = (float) $ligne->montant;
                if ($ligne->sens === 'debit') {
                    $solde += $montant;
                    fputcsv($output, [
                        $ligne->ecriture->date_ecriture->format('d/m/Y'),
                        $ligne->ecriture->numero,
                        $ligne->libelle_ligne ?? $ligne->ecriture->libelle,
                        number_format($montant, 2, ',', ''),
                        '',
                        number_format($solde, 2, ',', ''),
                    ]);
                } else {
                    $solde -= $montant;
                    fputcsv($output, [
                        $ligne->ecriture->date_ecriture->format('d/m/Y'),
                        $ligne->ecriture->numero,
                        $ligne->libelle_ligne ?? $ligne->ecriture->libelle,
                        '',
                        number_format($montant, 2, ',', ''),
                        number_format($solde, 2, ',', ''),
                    ]);
                }
            }
            fclose($output);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="grand-livre-' . $compte->code . '.csv"',
        ]);
    }
}
