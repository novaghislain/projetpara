<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des sessions utilisateur.
 * Permet de lister, révoquer les sessions actives.
 */
class UserSessionController extends Controller
{
    /**
     * Contrôleur de gestion des sessions utilisateur.
     * Permet de lister les sessions actives, de révoquer des sessions
     * spécifiques et de consulter l'historique des connexions.
     */

    /**
     * Liste les sessions actives de l'utilisateur connecté.
     *
     * @return JsonResponse La liste des sessions avec leurs métadonnées
     */
    public function activeSessions(): JsonResponse
    {
        $userId = Auth::id();
        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($s) {
                $payload = $s->payload ? @unserialize(base64_decode($s->payload)) : null;
                return [
                    'id'         => $s->id,
                    'ip_address' => $s->ip_address,
                    'user_agent' => $s->user_agent,
                    'last_active'=> $s->last_activity ? now()->createFromTimestamp($s->last_activity)->diffForHumans() : '---',
                    'is_current' => $s->id === session()->getId(),
                ];
            });

        return response()->json($sessions);
    }

    /**
     * Révoque une session spécifique (sauf la session actuelle).
     *
     * @param string $sessionId L'identifiant de la session à révoquer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revokeSession(string $sessionId)
    {
        // Empêche la révocation de sa propre session
        if ($sessionId === session()->getId()) {
            return back()->withErrors(['Vous ne pouvez pas révoquer votre session actuelle.']);
        }

        DB::table('sessions')->where('id', $sessionId)->delete();

        // Traçage de l'action dans l'audit
        AuditTrail::create([
            'user_id'   => Auth::id(),
            'event'     => 'logout',
            'auditable_type' => User::class,
            'auditable_id'   => Auth::id(),
            'description'    => 'Révoqué une session distante',
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);

        return back()->with('success', 'Session révoquée.');
    }

    /**
     * Révoque toutes les sessions sauf la session actuelle.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revokeOthers()
    {
        $currentSessionId = session()->getId();

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', $currentSessionId)
            ->delete();

        // Traçage de l'action dans l'audit
        AuditTrail::create([
            'user_id'   => Auth::id(),
            'event'     => 'logout',
            'auditable_type' => User::class,
            'auditable_id'   => Auth::id(),
            'description'    => 'Révoqué toutes les autres sessions',
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);

        return back()->with('success', 'Toutes les autres sessions ont été révoquées.');
    }

    /**
     * Historique des connexions (30 dernières entrées).
     *
     * @return JsonResponse L'historique des connexions
     */
    public function loginHistory(): JsonResponse
    {
        $history = AuditTrail::where('user_id', Auth::id())
            ->whereIn('event', ['login', 'logout', 'failed_login'])
            ->latest()
            ->take(30)
            ->get(['id', 'event', 'ip_address', 'user_agent', 'created_at']);

        return response()->json($history);
    }
}
