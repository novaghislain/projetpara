<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::where('client_id', Auth::user()->client_id)
            ->with('user:id,name')
            ->latest()
            ->paginate(15);
            
        return response()->json($reservations);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:salle,materiel,vehicule',
            'resource_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();
        $data['client_id'] = Auth::user()->client_id;
        $data['status'] = 'confirmé';

        $reservation = Reservation::create($data);

        return response()->json(['message' => 'Réservation créée avec succès', 'reservation' => $reservation]);
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorizeAccess($reservation);
        $reservation->delete();
        return response()->json(['message' => 'Réservation supprimée']);
    }

    private function authorizeAccess(Reservation $reservation)
    {
        if ($reservation->client_id !== Auth::user()->client_id) {
            abort(403);
        }
    }
}
