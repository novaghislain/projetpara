<?php
// @deprecated — Ces contrôleurs sont obsolètes. Voir README.md dans le dossier parent.

namespace App\Http\Controllers\Company\Compta\Modules;

use App\Http\Controllers\Controller;
use App\Models\AccountingHotelChambre;
use App\Models\AccountingHotelReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelChambreController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    public function index()
    {
        $clientId = $this->getClientId();
        $chambres = AccountingHotelChambre::forClient($clientId)->get();
        return response()->json($chambres);
    }

    public function store(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'numero' => 'required|string|max:20',
            'type' => 'required|string|max:50',
            'prix_nuitee' => 'required|numeric|min:0',
            'statut' => 'nullable|string|max:20',
        ]);
        $validated['client_id'] = $clientId;
        $chambre = AccountingHotelChambre::create($validated);
        return response()->json(['message' => 'Chambre créée.', 'chambre' => $chambre], 201);
    }

    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $chambre = AccountingHotelChambre::forClient($clientId)->findOrFail($id);
        $chambre->update($request->all());
        return response()->json(['message' => 'Chambre mise à jour.', 'chambre' => $chambre]);
    }

    public function reservations()
    {
        $clientId = $this->getClientId();
        $reservations = AccountingHotelReservation::forClient($clientId)->with('chambre')->get();
        return response()->json($reservations);
    }

    public function storeReservation(Request $request)
    {
        $clientId = $this->getClientId();
        $validated = $request->validate([
            'chambre_id' => 'required|exists:accounting_hotel_chambres,id',
            'client_nom' => 'required|string|max:255',
            'date_arrivee' => 'required|date',
            'date_depart' => 'required|date|after:date_arrivee',
            'montant' => 'required|numeric|min:0',
        ]);
        $validated['client_id'] = $clientId;
        $reservation = AccountingHotelReservation::create($validated);
        return response()->json(['message' => 'Réservation créée.', 'reservation' => $reservation], 201);
    }
}
