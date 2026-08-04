<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur du journal d'audit.
 * Permet de consulter et filtrer les traces d'audit (AuditTrail)
 * par événement, utilisateur, modèle, adresse IP ou période.
 */
class AuditController extends Controller
{
    /**
     * Affiche la liste paginée des traces d'audit.
     * Supporte le filtrage par événement, utilisateur, modèle, adresse IP
     * et intervalle de dates (from/to).
     *
     * @param Request $request La requête HTTP contenant les filtres (event, user, model, ip, from, to)
     * @return View
     */
    public function index(Request $request): View
    {
        $query = AuditTrail::with('user');

        // Filtrer par type d'événement (ex: login, cabinet_update, etc.)
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        // Filtrer par utilisateur (ID ou email partiel)
        if ($request->filled('user')) {
            $query->where(function ($q) use ($request) {
                $q->where('user_id', $request->user)
                  ->orWhereHas('user', fn($q) => $q->where('email', 'like', "%{$request->user}%"));
            });
        }
        // Filtrer par type de modèle audité (ex: App\Models\Client)
        if ($request->filled('model')) {
            $query->where('auditable_type', 'like', "%{$request->model}%");
        }
        // Filtrer par adresse IP
        if ($request->filled('ip')) {
            $query->where('ip_address', $request->ip);
        }
        // Filtrer par date de début
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        // Filtrer par date de fin
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $logs = $query->latest()->paginate(50);

        return view('app', ['page' => 'gel-audit', 'props' => compact('logs')]);
    }
}
