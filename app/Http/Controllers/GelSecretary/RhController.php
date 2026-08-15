<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\Salarie;
use App\Models\Gel\Bulletin;
use App\Models\Gel\Client;
use App\Services\Paie\ItScalculator;
use App\Services\Paie\CnssCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur RH (Secrétariat GEL) — Registre du personnel + Bulletins de paie de base.
 * CNSS Bénin : part salariale 3,36 % (plafond mensuel 450 000 F), employeur 15,40 %
 * (barème officiel — délégué à CnssCalculator).
 * ITS (CGI 2026 — jamais « ITS ») : barème MENSuel progressif 0/10/15/19/30 %
 * (0-60k, 60-150k, 150-250k, 250-500k, >500k) + redevance ORTB mars/juin.
 */
class RhController extends Controller
{

    /* =========================================================
     *  SALARIÉS
     * ======================================================= */

    /** Liste des salariés du client actif (ou client_id passé en query). */
    public function salaries(Request $request)
    {
        $user     = Auth::user();
        $clientId = session('gel_client_id') ?? $request->client_id;
        $client   = Client::where('id', $clientId)->firstOrFail();

        $salaries = Salarie::where('client_id', $clientId)
            ->orderBy('nom')
            ->get();

        return view('gel-secretary.rh.salaries.index', compact('client', 'salaries'));
    }

    /** Enregistre un nouveau salarié. */
    public function storeSalarie(Request $request)
    {
        $validated = $request->validate([
            'client_id'             => 'required|exists:gel_clients,id',
            'nom'                   => 'required|string|max:100',
            'prenom'                => 'required|string|max:100',
            'date_embauche'         => 'nullable|date',
            'salaire_base'          => 'required|numeric|min:0',
            'numero_cnss'           => 'nullable|string|max:50',
            'situation_matrimoniale'=> 'nullable|string|max:30',
            'nombre_enfants'        => 'nullable|integer|min:0',
        ]);

        Salarie::create($validated);

        return back()->with('success', 'Salarié ajouté avec succès.');
    }

    /** Supprime (soft) un salarié. */
    public function destroySalarie($id)
    {
        Salarie::findOrFail($id)->delete();
        return back()->with('success', 'Salarié archivé.');
    }

    /* =========================================================
     *  BULLETINS DE PAIE
     * ======================================================= */

    /** Affiche la page des bulletins pour un client / mois. */
    public function paie(Request $request)
    {
        $user     = Auth::user();
        $clientId = session('gel_client_id') ?? $request->client_id;
        $client   = Client::where('id', $clientId)->firstOrFail();

        $mois  = $request->mois  ?? date('m');
        $annee = $request->annee ?? date('Y');

        $salaries = Salarie::where('client_id', $clientId)
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get();

        $bulletins = Bulletin::where('client_id', $clientId)
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->with('salarie')
            ->get()
            ->keyBy('salarie_id');

        return view('gel-secretary.rh.paie.index', compact('client', 'salaries', 'bulletins', 'mois', 'annee'));
    }

    /** Génère (ou remet à jour) les bulletins du mois pour tous les salariés actifs. */
    public function genererBulletins(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:gel_clients,id',
            'mois'      => 'required|integer|min:1|max:12',
            'annee'     => 'required|integer|min:2020',
        ]);

        $clientId = $request->client_id;
        $mois     = $request->mois;
        $annee    = $request->annee;

        $salaries = Salarie::where('client_id', $clientId)->where('statut', 'actif')->get();

        $itsCalc = new ItScalculator();

        foreach ($salaries as $s) {
            $brut  = (float) $s->salaire_base;

            // CNSS — part salariale 3,36 % (plafond mensuel 450 000 F),
            // barème officiel (CnssCalculator).
            $cnss   = (new CnssCalculator())->calculate($brut)['part_salarie'];

            // ITS — barème mensuel progressif (CGI 2026, art. 125 §1).
            // Base d'imposition = salaire brut (art. 122). Pas d'abattement,
            // pas de quotient familial.
            $itsDetail = $itsCalc->calculerITS($brut);
            $its       = $itsDetail['its_mensuel'];

            // Redevance ORTB (art. 125 §2) : 1 000 F en mars, 3 000 F en juin
            // (exonéré du prélèvement de juin si revenu imposable ≤ 60 000 F).
            $ortbDetail = $itsCalc->calculerOrtb($brut, $mois);
            $ortb       = $ortbDetail['montant'];

            $net = round($brut - $cnss - $its - $ortb, 2);

            Bulletin::updateOrCreate(
                ['salarie_id' => $s->id, 'mois' => $mois, 'annee' => $annee],
                [
                    'client_id'    => $clientId,
                    'salaire_brut' => $brut,
                    'retenue_cnss' => $cnss,
                    // retenue ITS = ITS du mois + ORTB le cas échéant (retenue
                    // totale au profit de la DGI), faute de colonne dédiée.
                    'retenue_its'  => $its + $ortb,
                    'salaire_net'  => $net,
                    'statut'       => 'brouillon',
                    'deleted_at'   => null,
                ]
            );
        }

        return back()->with('success', "Bulletins de {$mois}/{$annee} générés avec succès (" . count($salaries) . " salarié(s)).");
    }

    /** Valide un bulletin (passe en statut 'valide'). */
    public function validerBulletin($id)
    {
        $bulletin = Bulletin::findOrFail($id);
        $bulletin->update(['statut' => 'valide']);
        return back()->with('success', 'Bulletin validé.');
    }

    /* =========================================================
     *  MOTEUR ITS — Bénin (barème progressif MENSuel, CGI 2026 art. 125 §1)
     *  Délégué à ItScalculator : 0 % ≤60k | 10 % 60-150k | 15 % 150-250k
     *  | 19 % 250-500k | 30 % >500k — aucun quotient familial.
     *  (il n'y a plus de méthode locale : ItScalculator fait foi)
     * ======================================================= */
}
