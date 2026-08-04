<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur du fil d'activité et des notifications.
 * Gère l'affichage des actions récentes (AuditTrail), les notifications
 * utilisateur et le nettoyage des notifications obsolètes.
 */
class ActivityFeedController extends Controller
{
    /**
     * Constructeur : applique le middleware de permission pour l'accès aux logs.
     */
    public function __construct()
    {
        $this->middleware('permission:admin.logs');
    }

    /**
     * Affiche la page d'activité.
     * Récupère les 50 dernières actions (AuditTrail) et les 20 dernières notifications
     * de l'utilisateur connecté.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupération des 50 dernières activités avec l'utilisateur associé
        $activities = AuditTrail::with('user')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'user' => $a->user?->name ?? 'Système',
                'event' => $a->event,
                'description' => $a->description,
                'auditable_type' => $a->auditable_type,
                'created_at' => $a->created_at,
                'time_diff' => $a->created_at->diffForHumans(),
            ]);

        // Récupération des 20 dernières notifications pour l'utilisateur connecté
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->take(20)
            ->get();

        return view('app', [
            'page' => 'gel-activity',
            'props' => [
                'activities' => $activities,
                'notifications' => $notifications,
            ],
        ]);
    }

    /**
     * Récupère les activités récentes (API).
     * Permet un filtre optionnel par date (since) pour les mises à jour incrémentales.
     *
     * @param Request $request La requête HTTP contenant optionnellement le paramètre 'since' (date ISO)
     * @return \Illuminate\Http\JsonResponse Réponse JSON avec la liste des activités récentes
     */
    public function recent(Request $request)
    {
        $since = $request->get('since');

        $query = AuditTrail::with('user')->latest()->take(50);

        // Si un marqueur temporel est fourni, filtrer les activités plus récentes que celui-ci
        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $activities = $query->get()->map(fn($a) => [
            'id' => $a->id,
            'user' => $a->user?->name ?? 'Système',
            'event' => $a->event,
            'description' => $a->description,
            'created_at' => $a->created_at->toISOString(),
            'time_diff' => $a->created_at->diffForHumans(),
        ]);

        return response()->json([
            'success' => true,
            'activities' => $activities,
        ]);
    }

    /**
     * Marque une notification spécifique comme lue.
     * Vérifie que la notification appartient à l'utilisateur connecté.
     *
     * @param int $notificationId L'identifiant de la notification à marquer
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Marque toutes les notifications de l'utilisateur comme lues.
     * Met à jour en masse les notifications non lues.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Récupère le nombre de notifications non lues pour l'utilisateur connecté.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Supprime une notification spécifique.
     * Vérifie que la notification appartient à l'utilisateur connecté.
     *
     * @param int $notificationId L'identifiant de la notification à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNotification($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Nettoie les notifications de plus de 90 jours pour l'utilisateur connecté.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanOld()
    {
        // Supprimer les notifications datant de plus de 90 jours
        $deleted = Notification::where('user_id', Auth::id())
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        return response()->json([
            'success' => true,
            'deleted_count' => $deleted,
        ]);
    }
}
