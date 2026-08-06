<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans le dossier parent.

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use App\Models\AccountingPressingCommande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PressingController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function commandes()
    {
        $clientId = $this->getClientId();
        $commandes = AccountingPressingCommande::forClient($clientId)->get();
        return response()->json($commandes);
    }

    public function storeCommande(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'client_nom' => 'required|string|max:255',
            'client_contact' => 'nullable|string|max:50',
            'type_service' => 'required|string|max:100',
            'montant_total' => 'required|numeric|min:0',
            'acompte' => 'nullable|numeric|min:0',
            'date_depot' => 'nullable|date',
            'date_retrait_prevu' => 'nullable|date',
        ]);
        $validated['client_id'] = $clientId;
        $validated['numero_commande'] = 'CMD-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $validated['statut'] = 'en_cours';
        $commande = AccountingPressingCommande::create($validated);
        return response()->json(['message' => 'Commande créée.', 'commande' => $commande], 201);
    }

    public function updateCommande(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $commande = AccountingPressingCommande::forClient($clientId)->findOrFail($id);
        $commande->update($request->all());
        return response()->json(['message' => 'Commande mise à jour.', 'commande' => $commande]);
    }

    public function changeStatut(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate(['statut' => 'required|string|max:20']);
        $commande = AccountingPressingCommande::forClient($clientId)->findOrFail($id);
        $commande->update(['statut' => $validated['statut']]);
        return response()->json(['message' => 'Statut mis à jour.']);
    }
}
