<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Gel\GelFacture;
use App\Models\Gel\GelFactureLigne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    public function index()
    {
        $factures = GelFacture::with('lignes')->orderBy('date_facture', 'desc')->get();
        return response()->json($factures);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entreprise_id' => 'required|uuid',
            'client_id' => 'required|integer',
            'type' => 'required|string',
            'numero' => 'nullable|string',
            'client_nom' => 'required|string',
            'date_facture' => 'required|date',
            'statut' => 'required|string',
            'lignes' => 'required|array|min:1',
            'lignes.*.designation' => 'required|string',
            'lignes.*.description' => 'nullable|string',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva' => 'required|numeric|min:0',
            'lignes.*.compte_produit_id' => 'nullable|uuid',
        ]);

        DB::beginTransaction();

        try {
            $total_ht = 0;
            $total_tva = 0;
            
            foreach ($validated['lignes'] as $ligne) {
                $ligne_ht = $ligne['quantite'] * $ligne['prix_unitaire'];
                $ligne_tva = $ligne_ht * ($ligne['taux_tva'] / 100);
                $total_ht += $ligne_ht;
                $total_tva += $ligne_tva;
            }

            $total_ttc = $total_ht + $total_tva;

            $facture = GelFacture::create([
                'entreprise_id' => $validated['entreprise_id'],
                'client_id' => $validated['client_id'],
                'type' => $validated['type'],
                'numero' => $validated['numero'] ?? 'FACT-' . time(),
                'client_nom' => $validated['client_nom'],
                'montant' => $total_ttc,
                'total_ht' => $total_ht,
                'total_tva' => $total_tva,
                'total_ttc' => $total_ttc,
                'date_facture' => $validated['date_facture'],
                'statut' => $validated['statut'],
            ]);

            foreach ($validated['lignes'] as $ligne) {
                $ligne_ht = $ligne['quantite'] * $ligne['prix_unitaire'];
                $ligne_tva = $ligne_ht * ($ligne['taux_tva'] / 100);
                $ligne_ttc = $ligne_ht + $ligne_tva;

                GelFactureLigne::create([
                    'facture_id' => $facture->id,
                    'designation' => $ligne['designation'],
                    'description' => $ligne['description'] ?? null,
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'taux_tva' => $ligne['taux_tva'],
                    'total_ht' => $ligne_ht,
                    'total_ttc' => $ligne_ttc,
                    'compte_produit_id' => $ligne['compte_produit_id'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json($facture->load('lignes'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $facture = GelFacture::with('lignes')->findOrFail($id);
        return response()->json($facture);
    }

    public function update(Request $request, $id)
    {
        $facture = GelFacture::findOrFail($id);
        
        $validated = $request->validate([
            'statut' => 'required|string',
            // d'autres champs si besoin
        ]);

        $facture->update($validated);

        return response()->json($facture);
    }

    public function destroy($id)
    {
        $facture = GelFacture::findOrFail($id);
        $facture->delete();
        return response()->json(null, 204);
    }

    /**
     * Valider la facture et générer l'écriture comptable correspondante
     */
    public function validerFacture($id)
    {
        $facture = GelFacture::with('lignes')->findOrFail($id);

        if ($facture->statut !== 'Brouillon') {
            return response()->json(['error' => 'La facture est déjà validée'], 400);
        }

        DB::beginTransaction();
        try {
            $compteClient = DB::table('gel_comptes_comptables')->where('code', 'like', '411%')->limit(1)->value('id');
            $compteVente = DB::table('gel_comptes_comptables')->where('code', 'like', '701%')->limit(1)->value('id');
            $compteTVA = DB::table('gel_comptes_comptables')->where('code', 'like', '4431%')->limit(1)->value('id');

            // Création de l'écriture comptable (simplifiée, à enrichir avec les règles du plan comptable)
            $journal_ventes_id = DB::table('gel_journaux')->where('type', 'Ventes')->value('id');
            $exercice_id = DB::table('gel_exercices')->where('cloture', false)->value('id');

            if (!$journal_ventes_id || !$exercice_id) {
                throw new \Exception("Journal des ventes ou exercice ouvert introuvable.");
            }

            $ecriture = \App\Models\Gel\EcritureComptable::create([
                'cabinet_id' => \Illuminate\Support\Facades\Auth::user()->cabinet_id,
                'client_id' => $facture->client_id,
                'journal_id' => $journal_ventes_id,
                'exercice_id' => $exercice_id,
                'date_ecriture' => $facture->date_facture,
                'libelle' => 'Facture ' . $facture->numero . ' - ' . $facture->client_nom,
                'statut' => 'brouillon',
                'source_type' => GelFacture::class,
                'source_id' => $facture->id,
            ]);

            // Ligne 411 - Client (TTC)
            \App\Models\Gel\LigneEcriture::create([
                'ecriture_id' => $ecriture->id,
                'compte_id' => $compteClient,
                'libelle_ligne' => 'Créance client',
                'sens' => 'debit',
                'montant' => $facture->total_ttc,
            ]);

            // Ligne 701 - Ventes (HT) (On pourrait boucler sur les lignes pour répartir si divers produits)
            \App\Models\Gel\LigneEcriture::create([
                'ecriture_id' => $ecriture->id,
                'compte_id' => $compteVente,
                'libelle_ligne' => 'Vente de marchandises',
                'sens' => 'credit',
                'montant' => $facture->total_ht,
            ]);

            // Ligne 4431 - TVA facturée
            if ($facture->total_tva > 0) {
                \App\Models\Gel\LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $compteTVA,
                    'libelle_ligne' => 'TVA collectée',
                    'sens' => 'credit',
                    'montant' => $facture->total_tva,
                ]);
            }

            $facture->update([
                'statut' => 'Validée',
                'ecriture_comptable_id' => $ecriture->id,
            ]);

            DB::commit();

            return response()->json(['message' => 'Facture validée avec succès', 'facture' => $facture]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
