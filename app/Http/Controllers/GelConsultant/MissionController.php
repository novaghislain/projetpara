<?php

namespace App\Http\Controllers\GelConsultant;

use App\Http\Controllers\Controller;
use App\Models\Gel\ConsultantMission;
use App\Models\Gel\ConsultantAudit;
use App\Models\Document;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function show(ConsultantMission $mission)
    {
        $user = auth()->user();

        // SÉCURITÉ SERVEUR : vérifier que ce consultant est bien assigné à cette mission
        if ($mission->consultant_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce dossier.');
        }

        // SÉCURITÉ SERVEUR : vérifier que la mission n'est pas expirée
        if ($mission->isExpired()) {
            return redirect()->route('consultant.expired')
                ->withErrors(['access' => 'Votre accès à ce dossier a expiré le ' . $mission->end_date->format('d/m/Y') . '.']);
        }

        $mission->load(['entreprise', 'deliverables', 'audits.user']);

        // Journaliser la consultation du dossier
        ConsultantAudit::create([
            'mission_id' => $mission->id,
            'user_id'    => $user->id,
            'action'     => 'Consultation du dossier : ' . $mission->title,
            'ip_address' => request()->ip(),
        ]);

        return view('gel-consultant.missions.show', compact('mission'));
    }
}
