<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BusinessTripController extends Controller
{
    public function index()
    {
        $trips = BusinessTrip::where('client_id', Auth::user()->client_id)
            ->with('user:id,name')
            ->latest()
            ->paginate(15);
            
        $reservations = \App\Models\Reservation::where('client_id', Auth::user()->client_id)
            ->with('user:id,name')
            ->latest()
            ->paginate(15);

        return Inertia::render('Company/Secretariat/Index', [
            'trips' => $trips,
            'reservations' => $reservations
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'destination' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'purpose' => 'required|string',
            'budget' => 'nullable|numeric',
            'transport_details' => 'nullable|string',
            'accommodation_details' => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();
        $data['client_id'] = Auth::user()->client_id;
        $data['status'] = 'en_attente';

        $trip = BusinessTrip::create($data);

        return response()->json(['message' => 'Voyage créé avec succès', 'trip' => $trip]);
    }

    public function update(Request $request, BusinessTrip $businessTrip)
    {
        $this->authorizeAccess($businessTrip);

        $data = $request->validate([
            'status' => 'sometimes|string|in:en_attente,approuvé,rejeté,terminé',
        ]);

        $businessTrip->update($data);

        return response()->json(['message' => 'Statut mis à jour', 'trip' => $businessTrip]);
    }
    
    private function authorizeAccess(BusinessTrip $trip)
    {
        if ($trip->client_id !== Auth::user()->client_id) {
            abort(403);
        }
    }
}
