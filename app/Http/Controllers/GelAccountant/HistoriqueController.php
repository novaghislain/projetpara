<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use App\Models\Gel\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoriqueController extends Controller
{
    /**
     * Affiche l'historique de traçabilité complet dans l'espace comptable.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = AuditLog::where('cabinet_id', $user->cabinet_id)
            ->with(['client', 'user'])
            ->latest();

        // Filtre Recherche textuelle (acteur, description, etc)
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

        // Filtre type d'événement
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Récupérer la liste des clients pour le filtre
        $clients = \App\Models\Gel\Client::where('cabinet_id', $user->cabinet_id)->actif()->get(['id', 'nom_entreprise']);

        // Différents types d'événements disponibles
        $events = AuditLog::where('cabinet_id', $user->cabinet_id)
            ->distinct()
            ->pluck('event');

        return view('gel-accountant.comptabilite.historique.index', compact('logs', 'clients', 'events') + [
            'currentSection' => 'comptabilite',
            'currentPage' => 'historique'
        ]);
    }

    /**
     * Affiche l'historique d'administration globale.
     */
    public function adminIndex(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Seul l'administrateur a accès à cette partie globale
        if (!$user->isSuperAdmin() && $user->account_type !== 'admin') {
            abort(403, 'Accès non autorisé.');
        }

        $query = AuditLog::with(['client', 'user', 'cabinet'])->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('actor_name', 'like', "%{$q}%")
                    ->orWhere('actor_email', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%");
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $logs = $query->paginate(30)->withQueryString();
        $events = AuditLog::distinct()->pluck('event');

        return view('gel-accountant.historique.index', compact('logs', 'events') + [
            'currentSection' => 'historique',
            'currentPage' => 'historique-admin'
        ]);
    }
}
