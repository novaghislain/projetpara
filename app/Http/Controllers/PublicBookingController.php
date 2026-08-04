<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Dae\DaeAgendaEvent;
use Carbon\Carbon;

class PublicBookingController extends Controller
{
    protected array $motifs = [
        'Point comptable',
        'Bilan annuel',
        'Conseil fiscal',
        'Création d\'entreprise',
        'Dépôt de documents',
        'Autre',
    ];

    public function showForm($cabinetId = 1)
    {
        return view('public.booking.index', [
            'cabinetId' => $cabinetId,
            'motifs'    => $this->motifs,
        ]);
    }

    public function book(Request $request, $cabinetId = 1)
    {
        $request->validate([
            'motif' => 'required|string',
            'date'  => 'required|date|after_or_equal:today',
            'time'  => 'required|string',
            'email' => 'required|email',
            'nom'   => 'required|string|max:100',
        ]);

        // Identifier le client via son email pour rattacher l'event
        $client = Client::where('email', $request->email)->first();

        $start_at = Carbon::parse($request->date . ' ' . $request->time);
        $end_at   = (clone $start_at)->addMinutes(30);

        $description = "Rendez-vous pris en ligne par : {$request->nom} ({$request->email})\nMotif : {$request->motif}";
        if ($request->filled('message')) {
            $description .= "\nMessage : " . $request->message;
        }

        DaeAgendaEvent::create([
            'client_id'  => $client?->id,
            'title'      => "📅 RDV – {$request->nom}",
            'description' => $description,
            'type'        => 'rdv',
            'start_at'   => $start_at,
            'end_at'     => $end_at,
            'all_day'    => false,
            'location'   => 'À définir',
            'couleur'    => '#16A34A', // Green to distinguish online bookings
            'statut'     => 'a_venir',
            'created_by' => null,
        ]);

        return redirect()->route('public.booking.success');
    }

    public function success()
    {
        return view('public.booking.success');
    }
}
