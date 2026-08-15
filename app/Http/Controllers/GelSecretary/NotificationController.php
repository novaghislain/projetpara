<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $notifications = Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('gel-secretary.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->findOrFail($id);
        
        $notification->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Notification marquée comme lue.');
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
