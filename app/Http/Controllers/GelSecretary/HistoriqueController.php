<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Gel\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoriqueController extends Controller
{
    /**
     * Affiche l'historique de traçabilité complet dans l'espace Secrétaire.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Récupère les logs d'audit associés au cabinet de la secrétaire
        $query = AuditLog::where('cabinet_id', $user->cabinet_id)
            ->with(['client', 'user'])
            ->latest();

        // Filtre Recherche textuelle
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('actor_name', 'like', "%{$q}%")
                    ->orWhere('actor_email', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%");
            });
        }

        // Filtre client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filtre événement
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Liste des clients pour le filtrage
        $clients = \App\Models\Client::orderBy('company_name')->get();

        // Événements distincts
        $events = AuditLog::where('cabinet_id', $user->cabinet_id)
            ->distinct()
            ->pluck('event');

        return view('gel-secretary.historique.index', compact('logs', 'clients', 'events'));
    }
}
