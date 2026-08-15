<?php

namespace App\Http\Controllers\GelAccountant\Facturation\CRM;

use App\Http\Controllers\Controller;
use App\Models\Gel\Facture;
use App\Models\Gel\FactureLigne;
use App\Models\Gel\GelProduit;
use App\Models\CompanyCrmContact;
use App\Models\Gel\Journal;
use App\Models\Gel\EcritureComptable;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\ExerciceComptable;
use App\Models\Gel\CompteComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FactureController extends Controller
{
    public function index()
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $factures = Facture::where('client_id', $clientId)
            ->with(['contact'])
            ->orderByDesc('date_facture')
            ->paginate(20);

        return view('gel-accountant.facturation.factures.index', compact('factures'));
    }

    public function create()
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        
        $clients = CompanyCrmContact::where('client_id', $clientId)->orderBy('last_name')->get();
        $produits = GelProduit::where('client_id', $clientId)->orderBy('nom')->get();
        
        // Numérotation automatique
        $lastFacture = Facture::where('client_id', $clientId)->orderByDesc('id')->first();
        $nextNum = $lastFacture ? intval(substr($lastFacture->numero, -4)) + 1 : 1;
        $numeroFacture = 'FAC-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return view('gel-accountant.facturation.factures.create', compact('clients', 'produits', 'numeroFacture'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:company_crm_contacts,id',
            'numero' => 'required|string|unique:gel_factures,numero',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date|after_or_equal:date_facture',
            'notes' => 'nullable|string',
            'lignes' => 'required|array|min:1',
            'lignes.*.produit_id' => 'required|exists:gel_produits,id',
            'lignes.*.description' => 'required|string',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;

        return DB::transaction(function () use ($validated, $clientId) {
            $totalHt = 0;
            $totalTva = 0;

            $facture = Facture::create([
                'client_id' => $clientId,
                'contact_id' => $validated['contact_id'],
                'numero' => $validated['numero'],
                'date_facture' => $validated['date_facture'],
                'date_echeance' => $validated['date_echeance'],
                'statut' => 'brouillon',
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['lignes'] as $ligne) {
                $ligneHt = $ligne['quantite'] * $ligne['prix_unitaire'];
                // Application d'un taux de TVA par défaut (18%) pour ce lot
                $tauxTva = 18;
                $ligneTva = $ligneHt * ($tauxTva / 100);
                $ligneTtc = $ligneHt + $ligneTva;

                FactureLigne::create([
                    'facture_id' => $facture->id,
                    'produit_id' => $ligne['produit_id'],
                    'description' => $ligne['description'],
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'taux_tva' => $tauxTva,
                    'total_ht' => $ligneHt,
                    'total_ttc' => $ligneTtc,
                ]);

                $totalHt += $ligneHt;
                $totalTva += $ligneTva;
            }

            $facture->update([
                'montant_ht' => $totalHt,
                'montant_tva' => $totalTva,
                'montant_ttc' => $totalHt + $totalTva,
            ]);

            return redirect()->route('gel-accountant.facturation.factures.show', $facture->id)
                ->with('success', 'Facture créée avec succès.');
        });
    }

    public function show($id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $facture = Facture::where('client_id', $clientId)->with(['contact', 'lignes.produit'])->findOrFail($id);

        return view('gel-accountant.facturation.factures.show', compact('facture'));
    }

    public function validateInvoice($id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $facture = Facture::where('client_id', $clientId)->with(['contact.compteComptable', 'lignes.produit.compteComptable'])->findOrFail($id);

        if ($facture->statut !== 'brouillon') {
            return back()->with('error', 'Seule une facture en brouillon peut être validée.');
        }

        return DB::transaction(function () use ($facture, $clientId) {
            // Intégration Comptable
            $exercice = ExerciceComptable::where('client_id', $clientId)
                ->where('date_debut', '<=', $facture->date_facture)
                ->where('date_fin', '>=', $facture->date_facture)
                ->where('statut', 'ouvert')
                ->first();

            if (!$exercice) {
                return back()->with('error', 'Aucun exercice comptable ouvert pour la date de la facture.');
            }

            $journal = Journal::where('client_id', $clientId)->where('type', 'ventes')->first();
            if (!$journal) {
                return back()->with('error', 'Aucun journal de ventes configuré pour ce client.');
            }

            $compteTva = CompteComptable::where('client_id', $clientId)->where('numero', '4431')->first();
            if (!$compteTva && $facture->montant_tva > 0) {
                return back()->with('error', 'Le compte de TVA Facturée (4431) est introuvable.');
            }

            $compteClient = $facture->contact->compteComptable ?? CompteComptable::where('client_id', $clientId)->where('numero', 'like', '411%')->first();
            if (!$compteClient) {
                return back()->with('error', 'Le contact n\'a pas de compte comptable associé (411...).');
            }

            $ecriture = EcritureComptable::create([
                'client_id' => $clientId,
                'exercice_id' => $exercice->id,
                'journal_id' => $journal->id,
                'date_ecriture' => $facture->date_facture,
                'libelle' => 'Facture ' . $facture->numero . ' - ' . ($facture->contact->company ?? $facture->contact->last_name),
                'reference_document' => $facture->numero,
                'statut' => 'brouillon',
                'created_by' => request()->user()->id,
            ]);

            // Débit: Compte Client (TTC)
            LigneEcriture::create([
                'ecriture_id' => $ecriture->id,
                'compte_id' => $compteClient->id,
                'sens' => 'debit',
                'montant' => $facture->montant_ttc,
                'libelle' => 'Créance client ' . $facture->numero,
            ]);

            // Crédit: TVA
            if ($facture->montant_tva > 0) {
                LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $compteTva->id,
                    'sens' => 'credit',
                    'montant' => $facture->montant_tva,
                    'libelle' => 'TVA collectée sur ' . $facture->numero,
                ]);
            }

            // Crédit: Produits HT
            // On regroupe par compte comptable du produit
            $produitsGroupes = [];
            foreach ($facture->lignes as $ligne) {
                $compteProduit = $ligne->produit->compteComptable ?? CompteComptable::where('client_id', $clientId)->where('numero', 'like', '701%')->first();
                if (!$compteProduit) {
                    throw new \Exception("Le produit '{$ligne->produit->nom}' n'a pas de compte comptable (70...).");
                }
                if (!isset($produitsGroupes[$compteProduit->id])) {
                    $produitsGroupes[$compteProduit->id] = 0;
                }
                $produitsGroupes[$compteProduit->id] += $ligne->total_ht;
            }

            foreach ($produitsGroupes as $compteId => $montantHt) {
                LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $compteId,
                    'sens' => 'credit',
                    'montant' => $montantHt,
                    'libelle' => 'Vente sur ' . $facture->numero,
                ]);
            }

            $facture->update([
                'statut' => 'validee',
                'ecriture_id' => $ecriture->id,
            ]);

            return back()->with('success', 'Facture validée et intégrée en comptabilité !');
        });
    }

    public function downloadPdf($id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $facture = Facture::where('client_id', $clientId)->with(['contact', 'lignes.produit'])->findOrFail($id);

        $pdf = Pdf::loadView('gel-accountant.facturation.factures.pdf', compact('facture'));
        
        return $pdf->download($facture->numero . '.pdf');
    }

    public function destroy($id)
    {
        $clientId = request()->user()->active_client_id ?? request()->user()->client_id;
        $facture = Facture::where('client_id', $clientId)->findOrFail($id);

        if ($facture->statut !== 'brouillon') {
            return back()->with('error', 'Impossible de supprimer une facture validée.');
        }

        $facture->lignes()->delete();
        $facture->delete();

        return redirect()->route('gel-accountant.facturation.factures.index')
            ->with('success', 'Facture supprimée.');
    }
}
