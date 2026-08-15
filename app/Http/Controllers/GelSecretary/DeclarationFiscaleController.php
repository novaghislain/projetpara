<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Gel\DeclarationFiscale;
use App\Models\Gel\LigneEcriture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur Module Déclarations Fiscales (Lot 3)
 */
class DeclarationFiscaleController extends Controller
{
    /** Liste des déclarations du client. */
    public function index(Request $request)
    {
        $clientId = session('gel_client_id') ?? $request->client_id;
        $client   = Client::where('id', $clientId)->firstOrFail();

        $declarations = DeclarationFiscale::where('client_id', $clientId)
            ->orderBy('periode_annee', 'desc')
            ->orderBy('periode_mois', 'desc')
            ->get();

        return view('gel-secretary.declarations.index', compact('client', 'declarations'));
    }

    /** Calcule la TVA et génère le brouillon de déclaration pour le mois demandé. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'     => 'required|exists:gel_clients,id',
            'periode_mois'  => 'required|integer|min:1|max:12',
            'periode_annee' => 'required|integer|min:2020',
        ]);

        $clientId = $validated['client_id'];
        $mois     = str_pad($validated['periode_mois'], 2, '0', STR_PAD_LEFT);
        $annee    = $validated['periode_annee'];
        $periode  = "$annee-$mois";

        // Pour le MVP : On cherche les lignes d'écritures du client sur le mois,
        // sur les comptes de classe 4 (TVA).
        // 443% = TVA Facturée (Collectée)
        // 445% = TVA Récupérable (Déductible)
        
        $lignes = LigneEcriture::whereHas('ecriture', function($q) use ($clientId, $periode) {
                $q->where('client_id', $clientId)
                  ->where('date_ecriture', 'like', "$periode-%");
            })
            ->whereHas('compte', function($q) {
                $q->where('code', 'like', '443%')
                  ->orWhere('code', 'like', '445%')
                  ->orWhere('code', 'like', '70%'); // Pour estimer la base CA HT
            })
            ->with('compte')
            ->get();

        $tvaCollectee = 0;
        $tvaDeductible = 0;
        $chiffreAffaires = 0;

        foreach ($lignes as $ligne) {
            $code = $ligne->compte->code;
            $montant = (float) $ligne->montant;
            
            if (str_starts_with($code, '443')) {
                // TVA facturée : crédit = augmentation
                if ($ligne->sens === 'credit') {
                    $tvaCollectee += $montant;
                } else {
                    $tvaCollectee -= $montant;
                }
            } elseif (str_starts_with($code, '445')) {
                // TVA récupérable : débit = augmentation
                if ($ligne->sens === 'debit') {
                    $tvaDeductible += $montant;
                } else {
                    $tvaDeductible -= $montant;
                }
            } elseif (str_starts_with($code, '70')) {
                // Ventes : crédit = augmentation
                if ($ligne->sens === 'credit') {
                    $chiffreAffaires += $montant;
                } else {
                    $chiffreAffaires -= $montant;
                }
            }
        }

        $netAPayer = $tvaCollectee - $tvaDeductible;

        DeclarationFiscale::updateOrCreate(
            [
                'client_id' => $clientId,
                'type_impot'=> 'TVA',
                'periode_mois' => $validated['periode_mois'],
                'periode_annee' => $annee,
            ],
            [
                'montant_base' => $chiffreAffaires,
                'montant_impot'=> $netAPayer,
                'statut'       => 'brouillon',
                'details'      => [
                    'ca_taxable' => $chiffreAffaires,
                    'tva_collectee' => $tvaCollectee,
                    'tva_deductible' => $tvaDeductible,
                    'credit_tva_reporte' => 0, // MVP: pas de gestion de report
                ]
            ]
        );

        return back()->with('success', "Le brouillon de déclaration TVA pour $mois/$annee a été généré avec succès.");
    }

    /** Affiche les détails (Liasse) d'une déclaration. */
    public function show($id)
    {
        $declaration = DeclarationFiscale::findOrFail($id);
        $client = $declaration->client;
        return view('gel-secretary.declarations.show', compact('client', 'declaration'));
    }

    /** Met à jour le statut de la déclaration (workflow). */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|in:brouillon,preparee,deposee,validee',
        ]);

        $declaration = DeclarationFiscale::findOrFail($id);
        $declaration->update(['statut' => $validated['statut']]);

        return back()->with('success', "Le statut de la déclaration a été mis à jour.");
    }
}
