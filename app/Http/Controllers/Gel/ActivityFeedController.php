<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityFeedController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin.logs');
    }

    /**
     * Affiche la page d'activité.
     */
    public function index()
    {
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
     */
    public function recent(Request $request)
    {
        $since = $request->get('since');

        $query = AuditTrail::with('user')->latest()->take(50);

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
     * Marque une notification comme lue.
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Marque toutes les notifications comme lues.
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Récupère le nombre de notifications non lues.
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
     * Supprime une notification.
     */
    public function deleteNotification($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Nettoie les notifications de plus de 90 jours.
     */
    public function cleanOld()
    {
        $deleted = Notification::where('user_id', Auth::id())
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        return response()->json([
            'success' => true,
            'deleted_count' => $deleted,
        ]);
    }
}
