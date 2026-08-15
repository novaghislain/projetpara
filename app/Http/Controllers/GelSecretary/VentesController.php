<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gel\Facture;
use App\Models\Gel\FactureLigne;
use App\Models\Gel\GelProduit;
use App\Models\CompanyCrmContact;
use App\Models\Gel\CompteComptable;
use App\Models\Gel\EcritureComptable;
use App\Models\Gel\LigneEcriture;
use App\Models\Gel\JournalComptable;
use App\Models\Gel\ExerciceComptable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentesController extends Controller
{
    /**
     * Liste des factures (ventes)
     */
    public function index(Request $request)
    {
        $clientId = session('gel_client_id') ?? $request->client_id;

        if (!$clientId) {
            return redirect()->route('gel-secretary.dashboard')
                ->with('error', 'Veuillez sélectionner un client pour gérer la facturation.');
        }

        $factures = Facture::with('contact')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('gel-secretary.ventes.index', compact('factures', 'clientId'));
    }

    /**
     * Formulaire de création d'une facture
     */
    public function create(Request $request)
    {
        $clientId = session('gel_client_id') ?? $request->client_id;

        if (!$clientId) {
            return redirect()->route('gel-secretary.dashboard')
                ->with('error', 'Client non sélectionné.');
        }

        // On liste les contacts du client pour la facturation
        $contacts = CompanyCrmContact::where('client_id', $clientId)->get();
        
        // On liste les produits (catalogue)
        $produits = GelProduit::where('client_id', $clientId)->get();

        // Génération d'un numéro de facture (très basique pour l'exemple)
        $lastFacture = Facture::where('client_id', $clientId)->orderBy('id', 'desc')->first();
        $numero = 'FAC-' . date('Y') . '-' . str_pad($lastFacture ? $lastFacture->id + 1 : 1, 4, '0', STR_PAD_LEFT);

        return view('gel-secretary.ventes.create', compact('contacts', 'produits', 'numero', 'clientId'));
    }

    /**
     * Enregistrer une nouvelle facture
     */
    public function store(Request $request)
    {
        $clientId = session('gel_client_id') ?? $request->client_id;
        
        $request->validate([
            'contact_id' => 'required|exists:company_crm_contacts,id',
            'numero' => 'required|string',
            'date_facture' => 'required|date',
            'date_echeance' => 'nullable|date',
            'lignes' => 'required|array',
            'lignes.*.produit_id' => 'nullable|exists:gel_produits,id',
            'lignes.*.designation' => 'required|string',
            'lignes.*.quantite' => 'required|numeric|min:1',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calcul des totaux
            $montant_ht = 0;
            foreach ($request->lignes as $ligne) {
                $montant_ht += $ligne['quantite'] * $ligne['prix_unitaire'];
            }
            
            // On applique 18% de TVA par défaut
            $tva = $montant_ht * 0.18;
            $ttc = $montant_ht + $tva;

            $facture = Facture::create([
                'client_id' => $clientId,
                'contact_id' => $request->contact_id,
                'numero' => $request->numero,
                'date_facture' => $request->date_facture,
                'date_echeance' => $request->date_echeance,
                'montant_ht' => $montant_ht,
                'montant_tva' => $tva,
                'montant_ttc' => $ttc,
                'statut' => 'brouillon',
                'notes' => $request->notes,
            ]);

            foreach ($request->lignes as $ligne) {
                FactureLigne::create([
                    'facture_id' => $facture->id,
                    'produit_id' => $ligne['produit_id'] ?? null,
                    'designation' => $ligne['designation'],
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'montant_ht' => $ligne['quantite'] * $ligne['prix_unitaire'],
                ]);
            }

            DB::commit();
            return redirect()->route('gel-secretary.ventes.show', ['id' => $facture->id, 'client_id' => $clientId])
                ->with('success', 'Facture créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création de la facture: ' . $e->getMessage());
        }
    }

    /**
     * Aperçu de la facture
     */
    public function show(Request $request, $id)
    {
        $clientId = session('gel_client_id') ?? $request->client_id;
        $facture = Facture::with(['contact', 'lignes'])->where('client_id', $clientId)->findOrFail($id);

        return view('gel-secretary.ventes.show', compact('facture', 'clientId'));
    }

    /**
     * Valider la facture et générer l'écriture comptable
     */
    public function valider(Request $request, $id)
    {
        $clientId = session('gel_client_id') ?? $request->client_id;
        $facture = Facture::with(['contact', 'lignes'])->where('client_id', $clientId)->findOrFail($id);

        if ($facture->statut === 'validée') {
            return back()->with('error', 'Cette facture est déjà validée.');
        }

        DB::beginTransaction();
        try {
            $facture->update(['statut' => 'validée']);

            // Génération de l'écriture comptable
            // 1. Trouver le journal des Ventes (VT)
            $journal = JournalComptable::where('client_id', $clientId)
                ->where('code', 'VT')
                ->first();
                
            if (!$journal) {
                // Créer le journal s'il n'existe pas
                $journal = JournalComptable::create([
                    'client_id' => $clientId,
                    'code' => 'VT',
                    'libelle' => 'Journal des Ventes',
                    'type' => 'ventes'
                ]);
            }

            // 2. Trouver l'exercice actif
            $exercice = ExerciceComptable::where('client_id', $clientId)
                ->where('statut', 'ouvert')
                ->whereDate('date_debut', '<=', $facture->date_facture)
                ->whereDate('date_fin', '>=', $facture->date_facture)
                ->first();
                
            if (!$exercice) {
                throw new \Exception("Aucun exercice comptable ouvert pour la date de la facture.");
            }

            // 3. Créer l'entête de l'écriture
            $ecriture = EcritureComptable::create([
                'client_id' => $clientId,
                'journal_id' => $journal->id,
                'exercice_id' => $exercice->id,
                'date_ecriture' => $facture->date_facture,
                'numero_piece' => $facture->numero,
                'libelle' => 'Facture client ' . ($facture->contact ? $facture->contact->name : ''),
                'statut' => 'brouillon',
                'source' => 'facturation'
            ]);

            // 4. Lignes d'écriture
            
            // Débit 411 - Client (TTC)
            $compteClient = CompteComptable::where('client_id', $clientId)->where('numero', 'like', '411%')->first();
            if (!$compteClient) {
                throw new \Exception("Compte comptable client (411) non trouvé.");
            }
            LigneEcriture::create([
                'ecriture_id' => $ecriture->id,
                'compte_id' => $compteClient->id,
                'libelle' => 'Facture ' . $facture->numero,
                'debit' => $facture->montant_ttc,
                'credit' => 0,
            ]);

            // Crédit 701 - Vente (HT)
            $compteVente = CompteComptable::where('client_id', $clientId)->where('numero', 'like', '701%')->first();
            if (!$compteVente) {
                throw new \Exception("Compte comptable de vente (701) non trouvé.");
            }
            LigneEcriture::create([
                'ecriture_id' => $ecriture->id,
                'compte_id' => $compteVente->id,
                'libelle' => 'Facture ' . $facture->numero,
                'debit' => 0,
                'credit' => $facture->montant_ht,
            ]);

            // Crédit 443 - TVA Facturée
            if ($facture->montant_tva > 0) {
                $compteTva = CompteComptable::where('client_id', $clientId)->where('numero', 'like', '443%')->first();
                if (!$compteTva) {
                    throw new \Exception("Compte comptable de TVA facturée (443) non trouvé.");
                }
                LigneEcriture::create([
                    'ecriture_id' => $ecriture->id,
                    'compte_id' => $compteTva->id,
                    'libelle' => 'TVA sur facture ' . $facture->numero,
                    'debit' => 0,
                    'credit' => $facture->montant_tva,
                ]);
            }

            // Lier l'écriture à la facture
            $facture->update(['ecriture_id' => $ecriture->id]);

            DB::commit();
            return back()->with('success', 'Facture validée et écriture comptable générée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }
}
