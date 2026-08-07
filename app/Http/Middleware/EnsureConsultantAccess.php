<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureConsultantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Vérifie que l'utilisateur est bien un consultant
        if ($user->account_type !== 'consultant' && $user->role !== 'consultant') {
            abort(403, 'Accès réservé aux consultants.');
        }

        // Vérifie qu'il a au moins une mission active (non expirée)
        $activeMission = $user->consultantMissions()
            ->where('status', '!=', 'cloture')
            ->where('end_date', '>=', now()->toDateString())
            ->exists();

        if (!$activeMission) {
            return redirect()->route('consultant.expired')
                ->withErrors(['access' => 'Votre accès a expiré ou aucune mission active ne vous a été confiée.']);
        }

        return $next($request);
    }
}
