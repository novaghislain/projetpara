<?php

namespace App\Http\Controllers\GelConsultant;

use App\Http\Controllers\Controller;
use App\Models\Gel\ConsultantMission;
use App\Models\Gel\ConsultantAudit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $missions = ConsultantMission::where('consultant_id', $user->id)
            ->with('entreprise')
            ->orderBy('end_date', 'asc')
            ->get()
            ->map(function ($mission) {
                $mission->is_expired = $mission->isExpired();
                $mission->days_remaining = $mission->is_expired
                    ? 0
                    : now()->diffInDays($mission->end_date, false);
                return $mission;
            });

        // Journaliser la connexion au tableau de bord
        ConsultantAudit::create([
            'mission_id' => $missions->first()?->id,
            'user_id'    => $user->id,
            'action'     => 'Connexion au tableau de bord',
            'ip_address' => request()->ip(),
        ]);

        return view('gel-consultant.dashboard', compact('missions'));
    }
}
