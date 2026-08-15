<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\Client;
use App\Models\Gel\Conformite;
use App\Models\Gel\ConformiteAction;
use App\Services\Gel\ConformiteScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConformiteController extends Controller
{
    /**
     * Page Score de Conformité GEL® d'un client.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clientId = $request->get('client_id', $user->active_client_id);
        
        if (!$clientId && $user->userClients()->count() > 0) {
            $clientId = $user->userClients()->first()->client_id;
        }

        if (!$clientId) {
            return redirect()->route('gel-secretary.dashboard')->with('error', 'Aucun client sélectionné.');
        }

        $client   = Client::findOrFail($clientId);

        // Initialiser les obligations si nécessaire et recalculer le score
        ConformiteScoreService::initialiserObligations($client);
        $score = ConformiteScoreService::calculer($client);

        $items = Conformite::where('client_id', $client->id)
            ->with('actions', 'verifiedBy')
            ->orderByRaw("FIELD(statut, 'ko', 'expire', 'attention', 'ok')")
            ->get()
            ->groupBy('categorie');

        $alertes = ConformiteScoreService::alertesExpiration($client);

        return view('gel-secretary.conformite.index', compact(
            'client', 'score', 'items', 'alertes'
        ));
    }

    /**
     * Mettre à jour le statut d'une obligation.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'statut'          => 'required|in:ok,attention,ko,expire',
            'date_expiration' => 'nullable|date',
            'date_validation' => 'nullable|date',
            'notes'           => 'nullable|string|max:1000',
            'document'        => 'nullable|file|max:5120',
        ]);

        $item = Conformite::findOrFail($id);

        $data = [
            'statut'          => $request->statut,
            'date_expiration' => $request->date_expiration,
            'date_validation' => $request->date_validation,
            'notes'           => $request->notes,
            'verified_by'     => Auth::id(),
            'verified_at'     => now(),
        ];

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store("gel/conformite/{$item->client_id}", 'public');
            $data['document_path'] = $path;
        }

        $item->update($data);

        // Recalculer le score
        $client = Client::find($item->client_id);
        ConformiteScoreService::calculer($client);

        return back()->with('success', "Obligation « {$item->titre} » mise à jour.");
    }

    /**
     * Ajouter une action au plan d'action d'une obligation.
     */
    public function storeAction(Request $request, $conformiteId)
    {
        $request->validate([
            'titre'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'echeance'         => 'nullable|date',
            'document_attendu' => 'nullable|string|max:255',
        ]);

        $item = Conformite::findOrFail($conformiteId);

        ConformiteAction::create([
            'conformite_id'    => $item->id,
            'client_id'        => $item->client_id,
            'titre'            => $request->titre,
            'description'      => $request->description,
            'responsable_id'   => Auth::id(),
            'echeance'         => $request->echeance,
            'document_attendu' => $request->document_attendu,
            'statut'           => 'a_faire',
        ]);

        return back()->with('success', 'Action ajoutée au plan.');
    }

    /**
     * Marquer une action comme terminée.
     */
    public function completeAction($actionId)
    {
        $action = ConformiteAction::findOrFail($actionId);
        $action->update(['statut' => 'termine']);

        return back()->with('success', 'Action marquée comme terminée.');
    }

    /**
     * Générer le Passeport Entreprise GEL® (PDF).
     */
    public function passeport($clientId)
    {
        $client = Client::findOrFail($clientId);
        
        // Recalculer le score pour s'assurer qu'il est à jour
        ConformiteScoreService::initialiserObligations($client);
        $score = ConformiteScoreService::calculer($client);

        $items = Conformite::where('client_id', $client->id)
            ->orderByRaw("FIELD(statut, 'ko', 'expire', 'attention', 'ok')")
            ->get();
            
        // Récupérer quelques indicateurs (simulés ou réels)
        $ca = \App\Models\Gel\EcritureComptable::where('client_id', $client->id)
            ->with(['lignes' => function($q) {
                $q->where('sens', 'credit')->whereHas('compte', function($q2) {
                    $q2->where('numero', 'like', '7%');
                });
            }])
            ->get()
            ->flatMap->lignes
            ->sum('montant');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gel-secretary.conformite.passeport', compact('client', 'score', 'items', 'ca'));
        
        return $pdf->download("Passeport_GEL_{$client->nom_entreprise}_" . date('Ymd') . ".pdf");
    }
}
