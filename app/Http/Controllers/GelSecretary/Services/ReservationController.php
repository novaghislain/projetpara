<?php

namespace App\Http\Controllers\GelSecretary\Services;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $activeClientId = session('active_client_id');
        $activeClient = $activeClientId ? \App\Models\Gel\Client::find($activeClientId) : null;

        if (!$activeClient) {
            $upcomingReservations = collect();
            $pastReservations = collect();
        } else {
            $reservations = Reservation::where('client_id', $activeClient->id)
                ->orderBy('start_time', 'desc')
                ->get();
                
            $upcomingReservations = $reservations->filter(function($res) {
                return $res->end_time >= now();
            })->sortBy('start_time');
            
            $pastReservations = $reservations->filter(function($res) {
                return $res->end_time < now();
            });
        }

        return view('gel-secretary.services.reservations', compact('upcomingReservations', 'pastReservations', 'activeClient'));
    }

    public function store(Request $request)
    {
        $activeClientId = session('active_client_id');
        if (!$activeClientId) {
            return back()->with('error', 'Veuillez sélectionner un client.');
        }

        $validated = $request->validate([
            'type' => 'required|in:salle,vehicule,equipement',
            'resource_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'nullable|string'
        ]);

        $validated['user_id'] = Auth::id();
        $validated['client_id'] = $activeClientId;
        $validated['status'] = 'pending';

        Reservation::create($validated);

        return back()->with('success', 'Réservation ajoutée avec succès.');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled'
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut de la réservation mis à jour.');
    }

    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return back()->with('success', 'Réservation supprimée.');
    }
}
