<?php

namespace App\Http\Controllers\GelSecretary\Services;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Models\Gel\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessTripController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $query  = BusinessTrip::with('client')->orderBy('date_depart', 'desc');

        $activeClientId = $request->query('client_id') ?? session('active_client_id') ?? $user->active_client_id ?? $user->client_id;
        if ($activeClientId) {
            $query->where('client_id', $activeClientId);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $trips   = $query->get();
        $clients = Client::orderBy('nom_entreprise')->get();
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($trips);
        }

        return view('gel-secretary.services.business-trips', compact('trips', 'clients', 'activeClient'));
    }

    public function create()
    {
        return redirect()->route('gel-secretary.services.business-trips.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'traveler_name'         => 'required|string|max:120',
            'client_id'             => 'required|exists:clients,id',
            'destination'           => 'required|string|max:200',
            'date_depart'            => 'required|date',
            'date_retour'              => 'required|date|after_or_equal:date_depart',
            'status'                => 'required|in:planifie,en_cours,termine,annule',
            'budget'                => 'nullable|numeric|min:0',
            'purpose'               => 'nullable|string|max:300',
            'transport_details'     => 'nullable|string|max:300',
            'accommodation_details' => 'nullable|string|max:300',
        ]);

        $validated['user_id'] = Auth::id();
        $trip = BusinessTrip::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'trip' => $trip]);
        }

        return redirect()->route('gel-secretary.services.business-trips.index')
            ->with('success', 'Déplacement vers ' . $trip->destination . ' créé avec succès.');
    }

    public function show(BusinessTrip $businessTrip)
    {
        return redirect()->route('gel-secretary.services.business-trips.index');
    }

    public function edit(BusinessTrip $businessTrip)
    {
        $clients = Client::orderBy('nom_entreprise')->get();
        $trips   = BusinessTrip::with('client')->orderBy('date_depart', 'desc')->get();
        return view('gel-secretary.services.business-trips', compact('trips', 'clients', 'businessTrip'));
    }

    public function update(Request $request, BusinessTrip $businessTrip)
    {
        $validated = $request->validate([
            'traveler_name'         => 'required|string|max:120',
            'client_id'             => 'required|exists:clients,id',
            'destination'           => 'required|string|max:200',
            'date_depart'            => 'required|date',
            'date_retour'              => 'required|date|after_or_equal:date_depart',
            'status'                => 'required|in:planifie,en_cours,termine,annule',
            'budget'                => 'nullable|numeric|min:0',
            'purpose'               => 'nullable|string|max:300',
            'transport_details'     => 'nullable|string|max:300',
            'accommodation_details' => 'nullable|string|max:300',
        ]);

        $businessTrip->update($validated);

        return redirect()->route('gel-secretary.services.business-trips.index')
            ->with('success', 'Déplacement mis à jour avec succès.');
    }

    public function destroy(BusinessTrip $businessTrip)
    {
        $dest = $businessTrip->destination;
        $businessTrip->delete();

        return redirect()->route('gel-secretary.services.business-trips.index')
            ->with('success', 'Déplacement vers ' . $dest . ' supprimé.');
    }
}
