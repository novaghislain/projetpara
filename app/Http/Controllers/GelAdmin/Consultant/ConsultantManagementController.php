<?php

namespace App\Http\Controllers\GelAdmin\Consultant;

use App\Http\Controllers\Controller;
use App\Models\Gel\ConsultantMission;
use App\Models\Gel\ConsultantInvitation;
use App\Models\Gel\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class ConsultantManagementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $entreprise = $user->entreprise;

        $missions = ConsultantMission::where('entreprise_id', $entreprise->id)
            ->with(['consultant', 'deliverables'])
            ->orderByDesc('created_at')
            ->get();

        return view('gel-admin.consultants.index', compact('missions', 'entreprise'));
    }

    public function create()
    {
        return view('gel-admin.consultants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'specialty'   => 'required|string|in:Juriste,Fiscaliste,Expert RH,Informaticien,Gestion',
            'start_date'  => 'nullable|date',
            'end_date'    => 'required|date|after:today',
        ]);

        $user = auth()->user();
        $entreprise = $user->entreprise;

        // Créer la mission
        $mission = ConsultantMission::create([
            'entreprise_id' => $entreprise->id,
            'created_by'    => $user->id,
            'title'         => $request->title,
            'description'   => $request->description,
            'specialty'     => $request->specialty,
            'status'        => 'en_attente',
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
        ]);

        // Créer l'invitation sécurisée (token unique, expire après 72h)
        $token = Str::random(64);
        $invitation = ConsultantInvitation::create([
            'mission_id' => $mission->id,
            'email'      => $request->email,
            'token'      => $token,
            'expires_at' => now()->addHours(72),
        ]);

        // Envoyer l'email d'invitation
        $acceptUrl = route('consultant.invitation.show', ['token' => $token]);

        // Envoi via notification simple (peut être remplacé par un Mailable dédié)
        \Illuminate\Support\Facades\Notification::route('mail', $request->email)
            ->notify(new \App\Notifications\ConsultantInvitationNotification($mission, $acceptUrl));

        return redirect()->route('gel-admin.consultants.index')
            ->with('success', 'L\'invitation a été envoyée à ' . $request->email . '. Le lien expire dans 72h.');
    }

    public function show(ConsultantMission $mission)
    {
        $user = auth()->user();

        // S'assurer que cet admin gère bien cette mission
        if ($mission->entreprise_id !== $user->entreprise?->id) {
            abort(403);
        }

        $mission->load(['consultant', 'deliverables', 'audits.user']);

        return view('gel-admin.consultants.show', compact('mission'));
    }

    public function renew(Request $request, ConsultantMission $mission)
    {
        $user = auth()->user();
        if ($mission->entreprise_id !== $user->entreprise?->id) abort(403);

        $request->validate([
            'end_date' => 'required|date|after:today',
        ]);

        $mission->update(['end_date' => $request->end_date]);

        \App\Models\Gel\ConsultantAudit::create([
            'mission_id' => $mission->id,
            'user_id'    => $user->id,
            'action'     => 'Renouvellement d\'accès jusqu\'au ' . \Carbon\Carbon::parse($request->end_date)->format('d/m/Y'),
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'L\'accès du consultant a été prolongé jusqu\'au ' . \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') . '.');
    }

    public function revoke(ConsultantMission $mission)
    {
        $user = auth()->user();
        if ($mission->entreprise_id !== $user->entreprise?->id) abort(403);

        $mission->update([
            'end_date' => now()->subDay()->toDateString(),
            'status'   => 'cloture',
        ]);

        \App\Models\Gel\ConsultantAudit::create([
            'mission_id' => $mission->id,
            'user_id'    => $user->id,
            'action'     => 'Révocation manuelle de l\'accès par l\'administrateur',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'L\'accès du consultant a été révoqué immédiatement.');
    }
}
