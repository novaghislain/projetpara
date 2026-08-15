<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Gel\Facture;
use App\Models\Gel\FactureLigne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FactureController extends Controller
{
    /**
     * Get the client ID from navigation/session context.
     * Gel users (Accountant/Cabinet) manage multiple clients.
     */
    protected function getClientId(Request $request)
    {
        return $request->query('client_id');
    }

    public function index(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return response()->json(['error' => 'client_id missing'], 400);
        }

        $factures = Facture::with(['contact', 'lignes'])
            ->where('client_id', $clientId)
            ->orderBy('date_facture', 'desc')
            ->get();
            
        return response()->json($factures);
    }

    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);
        if (!$clientId) {
            return response()->json(['error' => 'client_id missing'], 400);
        }

        $validated = $request->validate([
            'contact_id' => 'required|integer',
            'numero' => 'nullable|string',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date',
            'statut' => 'required|string',
            'notes' => 'nullable|string',
            'lignes' => 'required|array|min:1',
            'lignes.*.produit_id' => 'nullable|integer',
            'lignes.*.description' => 'required|string',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.taux_tva' => 'required|numeric|min:0',
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

            $facture = Facture::create([
                'client_id' => $clientId,
                'contact_id' => $validated['contact_id'],
                'numero' => $validated['numero'] ?? 'FACT-' . time(),
                'date_facture' => $validated['date_facture'],
                'date_echeance' => $validated['date_echeance'] ?? null,
                'montant_ht' => $total_ht,
                'montant_tva' => $total_tva,
                'montant_ttc' => $total_ttc,
                'statut' => $validated['statut'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['lignes'] as $ligne) {
                $ligne_ht = $ligne['quantite'] * $ligne['prix_unitaire'];
                $ligne_tva = $ligne_ht * ($ligne['taux_tva'] / 100);
                $ligne_ttc = $ligne_ht + $ligne_tva;

                FactureLigne::create([
                    'facture_id' => $facture->id,
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'description' => $ligne['description'],
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'taux_tva' => $ligne['taux_tva'],
                    'total_ht' => $ligne_ht,
                    'total_ttc' => $ligne_ttc,
                ]);
            }

            DB::commit();

            return response()->json($facture->load('lignes'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $facture = Facture::with(['contact', 'lignes'])->where('client_id', $clientId)->findOrFail($id);
        return response()->json($facture);
    }

    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $facture = Facture::where('client_id', $clientId)->findOrFail($id);
        
        $validated = $request->validate([
            'statut' => 'required|string',
            // d'autres champs si besoin
        ]);

        $facture->update($validated);

        return response()->json($facture);
    }

    public function destroy(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $facture = Facture::where('client_id', $clientId)->findOrFail($id);
        $facture->delete();
        return response()->json(null, 204);
    }

    /**
     * Valider la facture et générer l'écriture comptable correspondante
     */
    public function validerFacture(Request $request, $id)
    {
        $clientId = $this->getClientId($request);
        $facture = Facture::with('lignes')->where('client_id', $clientId)->findOrFail($id);

        if ($facture->statut !== 'brouillon') {
            return response()->json(['error' => 'La facture est déjà validée'], 400);
        }

        // Garde anti-doublon (§2.2) : une écriture comptable existe déjà pour
        // cette facture → ne pas en créer une seconde (risque de doublon).
        if ($facture->ecriture_id) {
            return response()->json(['error' => 'Une écriture comptable existe déjà pour cette facture.'], 400);
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
                // Créer un journal des ventes par défaut si non trouvé
                $journal_ventes_id = DB::table('gel_journaux')->insertGetId([
                    'cabinet_id' => Auth::user()->tenant_id ?? 1,
                    'code' => 'VT',
                    'nom' => 'Ventes',
                    'type' => 'Ventes',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            if (!$exercice_id) {
                 // Si aucun exercice, fallback
                 throw new \Exception("Aucun exercice comptable ouvert trouvé.");
            }

            $ecriture = \App\Models\Gel\EcritureComptable::create([
                'cabinet_id' => Auth::user()->tenant_id ?? 1,
                'client_id' => $facture->client_id,
                'journal_id' => $journal_ventes_id,
                'exercice_id' => $exercice_id,
                'date_ecriture' => $facture->date_facture,
                'libelle' => 'Facture ' . $facture->numero,
                'statut' => 'brouillon',
                'source_type' => Facture::class,
                'source_id' => $facture->id,
            ]);

            // Ligne 411 - Client (TTC)
            if ($compteClient) {
                \App\Models\Gel\LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $compteClient,
                    'libelle_ligne' => 'Créance client',
                    'sens' => 'debit',
                    'montant' => $facture->montant_ttc,
                ]);
            }

            // Ligne 701 - Ventes (HT)
            if ($compteVente) {
                \App\Models\Gel\LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $compteVente,
                    'libelle_ligne' => 'Vente de marchandises',
                    'sens' => 'credit',
                    'montant' => $facture->montant_ht,
                ]);
            }

            // Ligne 4431 - TVA facturée
            if ($facture->montant_tva > 0 && $compteTVA) {
                \App\Models\Gel\LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $compteTVA,
                    'libelle_ligne' => 'TVA collectée',
                    'sens' => 'credit',
                    'montant' => $facture->montant_tva,
                ]);
            }

            $facture->update([
                'statut' => 'validee',
                'ecriture_id' => $ecriture->id,
            ]);

            DB::commit();

            return response()->json(['message' => 'Facture validée avec succès', 'facture' => $facture]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
